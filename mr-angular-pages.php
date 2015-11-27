<?php
/**
 * @package mr-angular-pages
 */
/*
Plugin Name: mr-angular-pages
Plugin URI: http://mrhenry.be
Description: JSON output to work with the Angular.js mr-angular-pages package.
Version: 0.0.1
Author: Mr. Henry
Author URI: http://mrhenry.be/
Text Domain: mr-angular-pages
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	die;
}

add_action('template_redirect', 'mr_angular_pages');

function mr_angular_pages () {
	$currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$path = str_replace(get_site_url(), '', $currentUrl);

	if ($path === '/api/pages.json') {
		mr_angular_pages_index();
		die;
	}
}

function mr_angular_pages_index () {
	$output = array();

	// i18n is not supported for now
	$output['i18n'] = array(
		'locales' => array('en'),
		'default' => 'en'
	);

	// Pages
	$pages = get_pages(array(
		'sort_column' => 'ID',
		'hierarchical' => '0'
	));

	$pages = array_filter($pages, function ($p) {
		return $p->post_status === 'publish' && $p->post_type === 'page';
	});

	$pages = array_map(function ($p) {
		$o = array();

		$o['id'] = $p->ID;
		$o['description'] = $p->post_excerpt;
		$o['translations'] = array(
			'locale' => 'en',
			'path_component' => '',
			'title' => $p->post_title
		);

		$type = get_page_template_slug($p->ID);

		if ($type !== '') {
			$type[0] = strtoupper($type[0]);

			$type = preg_replace_callback(
				'/[_|-]([a-z])/',
				function ($match) {
					return strtoupper($match[1]);
				},
				$type
			);

			$type = str_replace('.php', '', $type);
		} else {
			$type = 'ApplicationPage';
		}

		$o['type'] = $type;
		$o['id'] = $p->ID;

		return $o;
	}, $pages);

	$output['pages'] = $pages;

	// Make types unique
	$types = [];

	foreach ($pages as $p) {
		if ($p['type'] === 'ApplicationPage') {
			continue;
		}

		if (!isset($types[$p['type']])) {
			$types[$p['type']] = 'ApplicationPage';
		}
	}

	$output['types'] = $types;

	// Output
	header('Content-Type: application/json');
	print json_encode($output);
}

function mr_angular_pages_show ($id) {

}
