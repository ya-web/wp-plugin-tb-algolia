<?php

/**
 * Plugin Name: Tela Botanica Algolia Integration
 * Description: Custom indexing for Tela Botanica content into Algolia.
 * Version: 0.2.0
 * Author Name: Hans Lemuet <hans@etaminstudio.com>
 */

add_action(
	'plugins_loaded',
	function () {

		if(!defined('ALGOLIA_APPLICATION_ID') || !defined('ALGOLIA_ADMIN_API_KEY')) {
				// Unless we have access to the Algolia credentials, stop here.
			return;
		}

		if(!defined('ALGOLIA_PREFIX')) {
			define('ALGOLIA_PREFIX', 'prod_');
		}

		// Composer dependencies.
		require_once 'libs/autoload.php';

		// Local dependencies.
		require_once 'inc/InMemoryIndexRepository.php';
		require_once 'inc/PostsIndex.php';
		require_once 'inc/WpQueryRecordsProvider.php';

		// Buddypress specific depenencies.
		require_once 'inc/BuddypressGroupRecordsProvider.php';
		require_once 'inc/BuddypressGroupsIndex.php';

		// TelaBotanica dependencies.
		require_once 'inc/TelaBotanica/Utils.php';
		// evenements
		require_once 'inc/TelaBotanica/EvenementRecordsProvider.php';
		require_once 'inc/TelaBotanica/EvenementsIndexSettingsFactory.php';
		require_once 'inc/TelaBotanica/EvenementChangeListener.php';
		// projets
		require_once 'inc/TelaBotanica/ProjetRecordsProvider.php';
		require_once 'inc/TelaBotanica/ProjetsIndexSettingsFactory.php';
		require_once 'inc/TelaBotanica/ProjetChangeListener.php';

		$indexRepository = new \WpAlgolia\InMemoryIndexRepository();
		$algoliaClient = new \WpAlgolia\Client(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

		// Register article index.
		// $settings = new \WpAlgolia\TelaBotanica\PostsIndexSettingsFactory();
		// $recordsProvider = new \WpAlgolia\TelaBotanica\PostRecordsProvider();
		// $index = new \WpAlgolia\PostsIndex(ALGOLIA_PREFIX . 'posts', $algoliaClient, $settings->create(), $recordsProvider);
		// new \WpAlgolia\TelaBotanica\PostChangeListener($index);
		// $indexRepository->add('posts', $index);

		// Register "evenements" index.
		$settings = new \WpAlgolia\TelaBotanica\EvenementsIndexSettingsFactory();
		$recordsProvider = new \WpAlgolia\TelaBotanica\EvenementRecordsProvider();
		$index = new \WpAlgolia\PostsIndex(ALGOLIA_PREFIX . 'evenements', $algoliaClient, $settings->create(), $recordsProvider);
		new \WpAlgolia\TelaBotanica\EvenementChangeListener($index);
		$indexRepository->add('evenements', $index);

		// Register "projets" index.
		$settings = new \WpAlgolia\TelaBotanica\ProjetsIndexSettingsFactory();
		$recordsProvider = new \WpAlgolia\TelaBotanica\ProjetRecordsProvider();
		$index = new \WpAlgolia\BuddypressGroupsIndex(ALGOLIA_PREFIX . 'projets', $algoliaClient, $settings->create(), $recordsProvider);
		new \WpAlgolia\TelaBotanica\ProjetChangeListener($index);
		$indexRepository->add('projets', $index);

		// WP CLI commands.
		if (defined('WP_CLI') && WP_CLI) {
			require_once 'inc/Commands.php';
			$commands = new \WpAlgolia\Commands($indexRepository);
			WP_CLI::add_command('algolia', $commands);
		}

	}
);
