<?php

namespace WBCR\Titan\Page;

use WBCR\Titan\Plugin;

class Backup extends Base
{

    /**
     * The id of the page in the admin menu.
     *
     * Mainly used to navigate between pages.
     * @see   FactoryPages000_AdminPage
     *
     * @since 1.0.0
     * @var string
     */
    public $id = "bm_settings";

    public $type = 'page';

    /**
     * @var string
     */
    public $page_menu_dashicon = 'dashicons-backup';

    /**
     * @var bool
     */
    public $available_for_multisite = true;

    /**
     * @var bool
     */
    public $clearfy_collaboration = false;

    /**
     * @var bool
     */
    public $show_right_sidebar_in_options = false;

    /**
     * @var object|\WBCR\Titan\Views
     */
    public $view;

    /**
     * @param  Plugin  $plugin
     */
    public function __construct( $plugin )
    {
        $this->menu_title                  = __( 'Backup', 'wbcr-backup-master' );
        $this->page_menu_short_description = __( 'Manage backups', 'wbcr-backup-master' );

        $this->view = Plugin::app()->view();

        parent::__construct( $plugin );
    }

    /**
     * Requests assets (js and css) for the page.
     *
     * @return void
     * @since 1.0.0
     * @see   Wbcr_FactoryPages000_AdminPage
     *
     */
    public function assets( $scripts, $styles )
    {
        parent::assets( $scripts, $styles );

        $this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/titan-security.css' );
        $this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/firewall/firewall-dashboard.css' );
        $this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/backup.css' );
    }

    public function showPageContent()
    {
        $this->view->print_template( 'backup' );
    }
}
