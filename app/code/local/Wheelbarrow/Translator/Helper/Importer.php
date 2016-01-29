<?php

class Wheelbarrow_Translator_Helper_Importer extends Mage_Core_Helper_Abstract
{
    const CSV_SEPARATOR = ',';
    
    /**
     * 
     * Gets a list of locale codes used within store views.
     * 
     * @return array
     * Associative list of locale codes.
     */
    protected function _getLocales()
    {
        $stores = Mage::app()->getStores(true);
        $locales = array();
        foreach ($stores as $store) {
            $locale = $store->getConfig('general/locale/code');
            $locales[$locale] = $locale;
        }
        return $locales;
    }

    /**
     * 
     * Parses a translations CSV file and returns output as array.
     * 
     * @param string $file
     * Relative path to file being parsed.
     * 
     * @return Array
     * String-translation pairs extracted from the requested file.
     * 
     */
    protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $parser = new Varien_File_Csv();
            $parser->setDelimiter(self::CSV_SEPARATOR);
            $data = $parser->getDataPairs($file);
        }
        return $data;
    }
    
    /**
     * 
     * Creates an array of items prepared for the db's string and translation tables.
     * 
     * @param array $file_description
     * 
     * @return Array
     * List of items prepared for the translator's addMultipleItems() function.
     * 
     * @todo remove underscore or make it protected.
     */
    public function _processCsvFile($file_description)
    {
        $data = array();
        if (file_exists($file_description['fileName'])) {
            $translation_pairs = $this->_getFileData($file_description['fileName']);
            
            //@todo remove the fileName if it isn't necessary in there.
            //@todo find a better solution for handling areas, such as always submitting it.
            foreach ($translation_pairs as $string => $translation) {
                $data[] = array(
                        'string' => $string,
                        'translation' => $translation,
                        'locale' => $file_description['locale'],
                        'fileName' => $file_description['fileName'],
                        'module' => $file_description['moduleName'],
                        'areas' => isset($file_description['areas']) ? $file_description['areas'] : null,
                        'strict' => true
                );
            }
        }
        return $data;
    }
    
    /**
     * 
     * Creates a list of translation CSV files from each combination of area and module.
     * 
     * @return Array
     */
    protected function _getFilesList()
    {
        $files = array();
        foreach (array('frontend', 'adminhtml', 'install') as $area) {
            if (Mage::getConfig()->getNode($area . '/translate/modules')) {
                $modules = Mage::getConfig()->getNode($area . '/translate/modules');
                if ($modules->children()) {
                    $modules = $modules->children();
                    foreach ($modules as $moduleName => $info) {
                        $info = $info->asArray();
                        foreach ($info['files'] as $file) {
                            if (!isset($files[$file])) {
                                $files[$file] = array('modules' => array(), 'areas' => array());
                            }
                            
                            $files[$file]['modules'][$moduleName] = $moduleName;
                            
                            if (!isset($files[$file]['areas'][$moduleName])) {
                                $files[$file]['areas'][$moduleName] = array();
                            }
                            $files[$file]['areas'][$moduleName][] = $area;
                        }
                    }
                }
            }
        }
        return $files;
    }
    
    /**
     * 
     * Creates a list of translations pairs, and saves them into batches.
     * @todo change the function's name.
     */
    public function pushCsvFilesToQueue()
    {
        $files = $this->_getFilesList();
        $locales = $this->_getLocales();
        $basedir = Mage::getBaseDir('locale');
        $data = array();
        
        //Checking each locale if its folder exists in the file system.
        foreach ($locales as $locale) {
            if (file_exists(Mage::getBaseDir('locale') . DS . $locale)) {
                foreach ($files as $file => $file_data) {
                    $fileName = $basedir . DS . $locale . DS . $file;
                    //Process the file once for each module that uses it, because each translation
                    // item is specific to a module's scope.
                    foreach ($file_data['modules'] as $moduleName) {
                        $pairs = $this->_processCsvFile(array(
                                                            'locale' => $locale, 
                                                            'fileName' => $fileName, 
                                                            'moduleName' => $moduleName,
                                                            'areas' => $file_data['areas'][$moduleName]
                                                        ));
                        $data = array_merge($data, (array)$pairs);
                    }
                }
            }
        }
        
        //@todo move the batch formation to a sync or batch helper function.
        $batches = array_chunk($data, Mage::getStoreConfig('translator/options/batch_size'));
        foreach ($batches as $batch) {
            Mage::getModel('translator/cache')->createItem('batch', $batch);
        }
        
        //@todo maybe a wrapper function for this.
        Mage::helper('translator/sync')->setRegisterData(array(
                'completed' => 0, 
                'total' => count($data)
        ));
        
        //@todo add a return value.
    }
    
    /**
     * 
     * Creates a list of translation files for the resources table in the admin panel.
     * 
     * @return Array
     * 
     * @todo merge with previous function and use helper functions for the differences at the bottom.
     */
    public function getResources()
    {
        $files = $this->_getFilesList();
    
        $data = array();
        $locales = $this->_getLocales();
        $basedir = Mage::getBaseDir('locale');
        foreach ($locales as $locale) {
            if (file_exists(Mage::getBaseDir('locale') . DS . $locale)) {
                foreach ($files as $file => $file_data) {
                    //@todo only use a file if it acutally exists.
                    $fileName = $basedir . DS . $locale . DS . $file;
                    if (file_exists($fileName)) {
                        foreach ($file_data['modules'] as $moduleName) {
                            //@todo why on earth are we serializing this?!
                            $data[] = serialize(array(
                                                    'locale' => $locale,
                                                    'fileName' => $fileName,
                                                    'moduleName' => $moduleName,
                                                    'areas' => $file_data['areas'][$moduleName],
                                                    'status' => false
                                                ));
                        }
                    }
                }
            }
        }
        return $data;
    }
    
    /**
     * 
     * Imitates Mage_Core's process of looking for theme translation files for each area and locale,
     * and saves the resulting translation pairs as batches.
     * 
     */
    public function pushThemeCsvsToQueue() {
        $params = array('_type' => 'locale');
        $package = Mage::getDesign();
        $stores = Mage::app()->getStores(true);
        $data = array();

        foreach (array('frontend', 'adminhtml') as $area) {
            $package->setArea($area);

            $locales = array();
            foreach ($stores as $store) {
                $locale = $store->getConfig('general/locale/code');

                //Setting the package according to a store view set to this locale:
                Mage::app()->setCurrentStore($store);
                $package->setPackageName();
                
                $params['_area'] = $package->getArea();
                $params['_package'] = $package->getPackageName();
                $params['_theme'] = $package->getTheme('locale');
                
                if (empty($params['_theme'])) {
                    $params['_theme'] = $package->getFallbackTheme();
                    if (empty($params['_theme'])) {
                        $params['_theme'] = $package->getDefaultTheme();
                    }
                }
                //@todo refactor to remove the repreated code here.
                $dir = Mage::getBaseDir('design').DS.
                $params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
                $locale;
                $file = $dir . DS . 'translate.csv';

                if (!file_exists($file)) {
                    $params['_theme']   = $package->getDefaultTheme();

                    $dir = Mage::getBaseDir('design').DS.
                    $params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
                    $locale;

                    $file = $dir . DS . 'translate.csv';
                    
                    if (!file_exists($file)) {
                        if ($area == 'frontend') {
                            //@todo check if you can use the constants instead of static values.
                            $params['_package'] = 'base';
                        } else {
                            $params['_package'] = 'default';
                        }

                        $dir = Mage::getBaseDir('design').DS.
                        $params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
                        $locale;

                        $file = $dir . DS . 'translate.csv';
                    }
                }
                //For theme files, anything not in the admin scope should work with all modules.
                //@todo add areas.
                $module = ($area == 'adminhtml') ? 'Mage_Adminhtml' : '';
                $pairs = $this->_processCsvFile(array('locale' => $locale, 'fileName' => $file, 'moduleName' => $module));
                $data = array_merge($data, (array)$pairs);
            }
        }

        $batches = array_chunk($data, Mage::getStoreConfig('translator/options/batch_size'));
        foreach ($batches as $batch) {
            Mage::getModel('translator/cache')->createItem('batch', $batch);
        }
        Mage::helper('translator/sync')->setRegisterData(array(
                'completed' => 0, 
                'total' => count($data)
        ));
    }
}