<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * @var ConfigFunctions
     */
    private $configFunctions;

    public function __construct(ConfigFunctions $configFunctions)
    {
        $this->configFunctions = $configFunctions;
    }

    /**
     * given a URL: http://example.com/<something>
     * fetches in the tables if the page <something> exists and returns it
     * return 404 otherwise.
     */
    public function page(Request $request, string $page): View
    {
        $page = Page::enabled()->where('link', '/' . $page)->first();

        if ($page === null) {
            \abort(404);
        }

        $lang = Lang::get_lang(Configs::get_value('lang'));
        $lang['language'] = Configs::get_value('lang');

        $infos = $this->configFunctions->get_pages_infos();
        $title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
        $menus = Page::menu()->get();

        $contents = $page->content;
        $page_config = [];
        $page_config['show_hosted_by'] = false;
        $page_config['display_socials'] = false;

        return \view(
            'page',
            ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'menus' => $menus, 'contents' => $contents, 'page_config' => $page_config]
        );
    }

    /**
     * TODO: add function to allow the edition of pages.
     */
    public function edit(Request $request, string $page): void
    {
    }

    /**
     * TODO: add function to save the edition of pages.
     */
    public function save(Request $request, string $page): void
    {
    }
}
