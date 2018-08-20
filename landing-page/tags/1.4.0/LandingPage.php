<?php
/*
Plugin Name: Landing Page
Plugin URI: http://presstify.com/theme-manager/addons/landing-page
Description: Page d'attente de site
Version: 1.0.0
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\LandingPage;

use \tiFy\App\Plugin;

class LandingPage extends Plugin
{
    /**
     * Constructeur
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclenchement des événements
        $this->appAddAction('query_vars', null, 1);
        $this->appAddAction('pre_get_posts');
        $this->appAddAction('template_redirect', null, 99);

        // Déclaration des fonctions d'aide à la saisie
        require_once 'Helpers.php';
    }

    /**
     * Modification des variables de la requête WordPress
     *
     * @param array $aVars Tableau indexé des variables de la requête WordPress
     *
     * @return array
     */
    public function query_vars($aVars)
    {
        $aVars[] = 'tify_landing_page';
        return $aVars;
    }

    /**
     * Modification du comportement de la requête WordPress
     *
     * @param \WP_Query $wp_query Objet WP_Query
     *
     * @return void
     */
    public function pre_get_posts($wp_query)
    {
        // Bypass
        if (is_admin())
            return;
        if ($this->isAllowed())
            return;
        if ($this->isExpired())
            return;

        $wp_query->set('tify_landing_page', true);
    }

    /**
     * Redirection de template
     *
     * @return void
     */
    public function template_redirect()
    {
        if (!get_query_var('tify_landing_page'))
            return;

        $exists = false;

        if (!self::tFyAppConfig('template')) :
        elseif (!file_exists(STYLESHEETPATH . '/' . self::tFyAppConfig('template') . '.php')) :
        elseif (!file_exists(TEMPLATEPATH . '/' . self::tFyAppConfig('template') . '.php')) :
        else :
            $exists = true;
        endif;

        if (!$exists) :
            $message = "<h3>" . __('Le gabarit de la page d\'attente est introuvable', 'tify') . "</h3>";
            if (!empty(self::tFyAppConfig('template'))) :
                $message .= "<p>" . sprintf(__('Impossible de trouver le fichier %s dans le dossier du thème courant.', 'tify'), "<b>" . self::tFyAppConfig('template') . ".php</b>") . "</p>";
            else :
                $message .= "<p>" . sprintf(__('la propriété "template" de votre fichier de configuration devrait être renseignée.', 'tify'), self::tFyAppConfig('template') . ".php") . "</p>";
            endif;
            wp_die($message, __('Page d\'attente introuvable', 'tify'), 500);
        else :
            get_template_part(self::tFyAppConfig('template'));
        endif;
        exit;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Vérification d'autorisation d'affichage de la landing page
     *
     * @return bool
     */
    private function isAllowed()
    {
        if (!$user = wp_get_current_user())
            return false;

        if (is_bool(self::tFyAppConfig('allow_logged_in'))) :
            if (self::tFyAppConfig('allow_logged_in') === false) :
                return false;
            else :
                return is_user_logged_in();
            endif;
        elseif ($allowed_users = (is_string(self::tFyAppConfig('allow_logged_in'))) ? array_map('trim', explode(',', self::tFyAppConfig('allow_logged_in'))) : self::tFyAppConfig('allow_logged_in')) :
            return in_array($user->user_login, $allowed_users);
        endif;
    }

    /**
     * Vérification de la date d'expiration d'affichage de la landing page
     *
     * @return bool
     */
    private function isExpired()
    {
        if (self::tFyAppConfig('expiration'))
            return ((mysql2date('U', self::tFyAppConfig('expiration'))) < current_time('timestamp'));
    }
}
