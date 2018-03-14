<?php
/**
 * Vérifie si la page courante est la page d'attente
 *
 * @return bool
 */
function tify_landing_page_is()
{
    return (bool)get_query_var('tify_landing_page');
}