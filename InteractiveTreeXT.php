<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT; 

use Aura\Router\RouterContainer;
use Aura\Router\Map;
use Fig\Http\Message\RequestMethodInterface;
use fisharebest\Localization\Translation;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\InteractiveTreeModule;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleBlockTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use HuHwt\WebtreesMods\InteractiveTreeXT\InteractiveTreeXTmod;
use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod;
use HuHwt\WebtreesMods\InteractiveTreeXT\Traits\ModuleChartTrait;
use HuHwt\WebtreesMods\InteractiveTreeXT\Traits\ModuleTabTrait;
use HuHwt\WebtreesMods\InteractiveTreeXT\Configuration;

use intval;
/**
 * Class InteractiveTreeXT
 * 
 * @author  EW.H <GIT@HuHnetz.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/huhwt/huhwt-xtv/
 */

class InteractiveTreeXT extends InteractiveTreeModule implements ModuleGlobalInterface, ModuleCustomInterface, 
    ModuleChartInterface, ModuleTabInterface
{
    use ModuleCustomTrait;
    use ModuleChartTrait;
    use ModuleTabTrait;
    // use ViewResponseTrait;
    // use IndividualTrait;

    private const ROUTE_DEFAULT = 'treeXTV';
    private const ROUTE_URL = '/tree/{tree}/treeXTV';

    // Expansion level
    public const SHOW_NEXT  = 1;
    public const SHOW_ALL = 2;

    /**
     * The configuration instance.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * The label ...
     * @var string
     */
    private $huh;

    /**
     * Check for UksusoFF/webtrees-tree_view_full_screen done?
     * @var boolean
     */
    // private $wtfs_checked;

    public function __construct() {
      $this->huh = json_decode('"\u210D"') . "&" . json_decode('"\u210D"') . "wt";
    //   $this->wtfs_checked = false;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     *
     * @return string
     */
    public function customModuleAuthorName(): string {

        return 'EW.Heinrich';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     *
     * @return string
     */
    public function customModuleVersion(): string {
        return '2.1.17.4';
    }

    /**
     * {@inheritDoc}
     * A URL that will provide the latest stable version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string {
        return 'https://github.com/huhwt/huhwt-xtv/master/latest-version.txt';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     *
     * @return string
     */
    public function customModuleSupportUrl(): string {
        return 'https://github.com/huhwt/huhwt-xtv/issues';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     *
     * @return string
     */
    public function resourcesFolder(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return array<string,string>
     */
    public function customTranslations(string $language): array
    {
        // no differentiation according to language variants
        $_language = substr($language, 0, 2);
        $ret = [];
        $languageFile = $this->resourcesFolder() . 'lang' . DIRECTORY_SEPARATOR . $_language . '.po';
        if (file_exists($languageFile)) {
            $ret = (new Translation($languageFile))->asArray();
        }
        return $ret;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     *
     * @return string
     */
    public function title(): string 
    {
        $_title = I18N::translate('Interactive tree XT');
        return $_title . ' ' . $this->huh;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     *
     * @return string
     */
    public function description(): string 
    {
        return I18N::translate('An interactive tree, showing all the ancestors and descendants of an individual.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::headContent()
     * CSS class for the URL.
     *
     * EW.H - MOD ... we need our Script too, so we do a double injection
     * @return string
     */
    public function headContent(): string
    {
        $html_CSS = view("{$this->name()}::style", [
            'path' => $this->assetUrl('css/huhwt.min.css'),
        ]);
        $html_JSx = view("{$this->name()}::script", [
            'path' => $this->assetUrl('js/huhwtXT.min.js'),
        ]);
        $html_JSh = view("{$this->name()}::script", [
            'path' => $this->assetUrl('js/html2canvas.js'),
        ]);
        $html_ = $html_CSS . " " . $html_JSx . " " . $html_JSh;
        return $html_;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::bodyContent()
     * EW.H - MOD ... - ( see headConten() )
     * @return string
     */
    public function bodyContent(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void 
    {
        $router_container = app(RouterContainer::class);
        assert($router_container instanceof RouterContainer);

        $router = $router_container->getMap();

        $router->attach('', '/tree/{tree}', static function (Map $router) {
            // $router->extras([
            //     'middleware' => [
            //         AuthManager::class,
            //     ],
            // ]);

            $router->get(InteractiveTreeXTmod::class, '/treeXTV')
                    ->allows(RequestMethodInterface::METHOD_POST);
            });

        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        View::registerCustomView('::modules/treeviewXT/tab', $this->name() . '::modules/treeviewXT/tab');
        View::registerCustomView('::modules/treeviewXT/chart', $this->name() . '::modules/treeviewXT/chart');
        View::registerCustomView('::modules/treeviewXT/page', $this->name() . '::modules/treeviewXT/page');
        View::registerCustomView('::modules/treeviewXT/pageh2', $this->name() . '::modules/treeviewXT/pageh2');
        View::registerCustomView('::modules/treeviewXT/pageh3', $this->name() . '::modules/treeviewXT/pageh3');

        //dependency check
        // if (!$this->wtfs_checked) {
        //     $ok = class_exists("UksusoFF\WebtreesModules\TreeViewFullScreen\Modules\TreeViewFullScreenModule", true);
        //     if (!$ok) {
        //         $wtfs_link = '(https://github.com/UksusoFF/webtrees-tree_view_full_screen)';
        //         $wtfs_missing = I18N::translate('Missing dependency - Install UksusoFF/webtrees-tree_view_full_screen!');
        //         $theMessage = $wtfs_missing . ' -> ' . $wtfs_link;
        //         FlashMessages::addMessage($theMessage);
        //     }
        //     $this->wtfs_checked = true;
        // }
    }

    /**
     * Actions Tab-Context
     */

#region     Tab-Context

    /**
     * The default position for this tab.  It can be changed in the control panel.
     *
     * @return int
     */
    public function defaultTabOrder(): int
    {
        return 7;
    }

    /**
     * Generate the HTML content of this tab.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function getTabContent(Individual $individual): string
    {
        $showpatri = intval((string) Configuration::PATRI_PRIO);
        $generations = intval((string) Configuration::DEFAULT_GENERATIONS);
        $module = $this->name();

        $tvPref = 'tv' . 'XT';
        $treeview = new TreeViewXTmod($tvPref, $module, $showpatri);

        $subtitleAr[] = $this->chartSubTitle($individual);
        [$html, $js] = $treeview->drawViewport($individual, 'XT', $generations, false);

        return view('modules/treeviewXT/tab', [
            'html'  => $html,
            'js'    => $js,
        ]);
    }

    /**
     * Is this tab empty? If so, we don't always need to display it.
     *
     * @param Individual $individual
     *
     * @return bool
     */
    public function hasTabContent(Individual $individual): bool
    {
        return $individual->facts(['FAMC', 'FAMS'])->isNotEmpty();
    }

    /**
     * A greyed out tab has no actual content, but may perhaps have
     * options to create content.
     *
     * @param Individual $individual
     *
     * @return bool
     */
    public function isGrayedOut(Individual $individual): bool
    {
        return false;
    }

    /**
     * Can this tab load asynchronously?
     *
     * @return bool
     */
    public function canLoadAjax(): bool
    {
        return true;
    }

#endregion

    /**
     * Actions Chart-Context
     */

#region     Chart-Context

     /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getChartAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();
        $xref = Validator::queryParams($request)->isXref()->string('xref');

        Auth::checkComponentAccess($this, ModuleChartInterface::class, $tree, $user);

        $this->configuration = new Configuration($request);

        if (stripos($xref,',') > 0) {
            $xrefs = explode ( ',' , $xref );
        } else {
            $xrefs[] = $xref;
        }
        $individualAr = [];
        foreach ($xrefs as $xref) {
            $individual = Registry::individualFactory()->make($xref, $tree);
            $individual = Auth::checkIndividualAccess($individual, false, true);
            $individualAr[] = $individual;
        }

        $patri_prio = (string) Configuration::PATRI_PRIO;
        $s_showpatri = Validator::queryParams($request)->string('showpatri', $patri_prio);
        $showpatri = intval($s_showpatri ?? $this->configuration->getPatriPrio());
        $s_generations = Validator::queryParams($request)->string('generations', '4');
        $generations = intval($s_generations ?? $this->configuration->getGenerations());

        $module = Validator::attributes($request)->string('module');

        $tvPrefix = $this->configuration->getTvPrefix();
        $htmlAr = [];
        $jsAr = [];
        $jsImp = [];
        $subtitleAr = [];

        // for ( $tvi = 0; $tvi < count($individualAr); $tvi++) {
            // if ($tvi == 0) {
            //     $html_JS = view("{$this->name()}::script", [
            //         'path' => $this->assetUrl('js/huhwtXT.min.js'),
            //     ]);
            //     $jsImp[] = $html_JS;
            // }
            $individual = $individualAr[0];
            $tvPref = 'tv' . $tvPrefix[0];
            $tv = new TreeViewXTmod($tvPref, $module, $showpatri);

            $subtitleAr[] = $this->chartSubTitle($individual);
            [$html, $js] = $tv->drawViewport($individual, $tvPrefix[0], $generations, true);

            $htmlAr[] = $html;
            $jsAr[] = $js;
        // }

        return $this->viewResponse('modules/treeviewXT/page', [
            'individuals'   => $individualAr,
            'showpatri'     => $showpatri,
            'generations'   => $generations,
            'htmls'         => $htmlAr,
            'jsimp'         => $jsImp,
            'jss'           => $jsAr,
            'module'        => $this->name(),
            'title'         => $this->chartTitle($individualAr[0]),
            'subtitles'     => $subtitleAr,
            'tree'          => $tree,
            ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postChartAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();

        $xref = Validator::parsedBody($request)->string('xref', '');
        $generations = Validator::parsedBody($request)->string('generations', '4');
        $patri_prio = (string) Configuration::PATRI_PRIO;
        $showpatri = Validator::parsedBody($request)->string('showpatri', $patri_prio);

        return redirect(route('module', [
            'module'        => $this->name(),
            'action'        => 'Chart',
            'tree'          => $tree->name(),
            'xref'          => $xref,
            'generations'   => $generations,
            'showpatri'     => $showpatri,
            ]));
    }

#endregion

}
