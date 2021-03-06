<?php
namespace Evoweb\SfRegister\Form;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class ItemProcessor
 *
 * @package Evoweb\SfRegister\Form
 */
class ItemProcessor
{
    /**
     * @param array $parameters
     */
    public function getConfiguredFields(&$parameters)
    {
        $items =& $parameters['items'];

        $configuredFields = $this->getConfiguredFieldsFromTsConfig();
        foreach ($configuredFields as $fieldName => $configuration) {
            $fieldName = rtrim($fieldName, '.');
            $label = $this->getLabel($fieldName, $configuration);
            $items[] = [$label, $fieldName];
        }
    }

    /**
     * @param string $fieldName
     * @param array $configuration
     *
     * @return string
     */
    protected function getLabel($fieldName, $configuration)
    {
        return isset($configuration['backendLabel']) ?
            $configuration['backendLabel'] :
            'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.' . $fieldName;
    }

    /**
     * @return array
     */
    protected function getConfiguredFieldsFromTsConfig()
    {
        $configuration = $this->getBackendUserAuthentication()
            ->getTSConfig('plugin.tx_sfregister.settings.fields.configuration');
        return $configuration['properties'];
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}
