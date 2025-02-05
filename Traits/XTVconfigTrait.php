<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2025 EW.Heinrich
 * 
 * Coding for the configuration in Admin-Panel goes here
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Traits;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait XTVconfigTrait {

    /**
     * Alternative starting option for Chart-View
     *
     * @return array<int,string>
     */
    public function chartConfigOptions(): array
    {
        return [
            0   => I18N::translate('Basic view'),
            1   => I18N::translate('Expanded view'),         // default
        ];
    }

    /**
     * Alternative starting option for Tab-View
     * 
     * @return array<int,string>
     */
    private function tabConfigOptions(): array
    {
        return [
            0   => I18N::translate('Basic view'),           // default
            1   => I18N::translate('Expanded view'),
        ];
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        return $this->viewResponse($this->name() . '::settings', [
            'chartOption'       => (int) $this->getPreference('chart_Option', '1'),
            'chart_options'     => $this->chartConfigOptions(),
            'tabOption'         => (int) $this->getPreference('tab_Option', '0'),
            'tab_options'       => $this->tabConfigOptions(),
            'title'             => I18N::translate('View preferences') . ' — ' . $this->title(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $chartOption = Validator::parsedBody($request)->integer('chartOption');
        $tabOption = Validator::parsedBody($request)->integer('tabOption');

        $this->setPreference('chart_Option', (string) $chartOption);
        $this->setPreference('tab_Option', (string) $tabOption);

        FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been updated.', $this->title()), 'success');

        return redirect($this->getConfigLink());
    }


}