<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
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

use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Api to get informations via ajax calls
 * Possible informations are static info tables country zones
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class AjaxController
{
    /**
     * Request parameters from url
     *
     * @var array
     */
    protected $requestArguments = [];

    /**
     * Status of the request returned with every response
     *
     * @var string
     */
    protected $status = 'success';

    /**
     * Message related to the status returned with every response
     *
     * @var string
     */
    protected $message = '';

    /**
     * Result of every action that gets returned with every response
     *
     * @var array
     */
    protected $result = [];


    /**
     * Dispatch the given action and call the output rendering afterwards
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return NULL|\Psr\Http\Message\ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestArguments = $request->getParsedBody()['tx_sfregister'];

        switch ($requestArguments['action']) {
            case 'zones':
                $this->getZonesAction($requestArguments['parent']);
                break;

            default:
                $this->errorAction();
        }

        $response->getBody()->write($this->output());

        return $response;
    }

    /**
     * @return void
     */
    protected function errorAction()
    {
        $this->status = 'error';
        $this->message = 'unknown action';
    }

    /**
     * @param int|string $parent
     *
     * @return void
     */
    protected function getZonesAction($parent)
    {
        /** @var StaticCountryZoneRepository $zoneRepository */
        $zoneRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(StaticCountryZoneRepository::class);

        if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($parent)) {
            $zones = $zoneRepository->findAllByParentUid((int) $parent);
        } else {
            $parent = strtoupper(preg_replace('/[^A-Za-z]{2}/', '', $parent));
            $zones = $zoneRepository->findAllByIso2($parent);
        }

        if ($zones->rowCount() == 0) {
            $this->status = 'error';
            $this->message = 'no zones';
        } else {
            $result = [];

            array_walk($zones->fetchAll(), function ($zone) use (&$result) {
                /** @var array $zone */
                $result[] = [
                    'value' => $zone['uid'],
                    'label' => $zone['zn_name_local'],
                ];
            });

            $this->result = $result;
        }
    }

    /**
     * Render the status, message and result as json encoded array as response
     *
     * @return string
     */
    protected function output(): string
    {
        $result = [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->result,
        ];

        return json_encode($result);
    }
}
