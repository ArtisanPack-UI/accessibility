<?php
/**
 * Manages discovery, registration, and lifecycle of Accessibility plugins.
 *
 * Discovers plugins from conventional directories and Composer-installed packages,
 * registers them, and handles initialization and lifecycle events.
 *
 * @since   2.0.0
 * @package ArtisanPack\Accessibility
 */

namespace ArtisanPack\Accessibility\Plugins;

use ArtisanPack\Accessibility\Plugins\Contracts\AccessibilityRulePluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\AnalysisToolPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\ColorFormatPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;
use Composer\InstalledVersions;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

/**
 * Plugin Manager.
 *
 * Responsible for discovering, registering, and managing the lifecycle of
 * Accessibility plugins from conventional directories and Composer packages.
 *
 * @since 2.0.0
 */
class PluginManager
{
	/**
	 * Registered plugins keyed by their unique id.
	 *
	 * @since 2.0.0
	 * @var array<string,PluginInterface>
	 */
	private array $plugins = [];

	/**
	 * Registered color format plugins.
	 *
	 * @since 2.0.0
	 * @var ColorFormatPluginInterface[]
	 */
	private array $colorFormatPlugins = [];

	/**
	 * Registered accessibility rule plugins.
	 *
	 * @since 2.0.0
	 * @var AccessibilityRulePluginInterface[]
	 */
	private array $rulePlugins = [];

	/**
	 * Registered analysis tool plugins.
	 *
	 * @since 2.0.0
	 * @var AnalysisToolPluginInterface[]
	 */
	private array $analysisPlugins = [];

	/**
	 * Logger instance.
	 *
	 * @since 2.0.0
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param Context $context Plugin execution context providing configuration and services.
	 */
	public function __construct( private Context $context )
	{
		$this->logger = $this->context->getLogger() ?? new NullLogger();
	}

	/**
	 * Discover plugins using configured mechanisms and register them.
	 *
	 * Searches conventional directories and Composer-installed packages, applies
	 * allow/deny lists, and optionally initializes and starts plugins based on
	 * configuration (safe_mode).
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function discoverAndRegister(): void
	{
		$config = $this->context->getConfig( 'plugins', [] );
		if ( ! (bool) ( $config['enabled'] ?? true ) ) {
			$this->logger->info( 'Plugins disabled by configuration.' );
			return;
		}

		// Conventional directories
		$paths = $config['paths'] ?? [ __DIR__ . '/../../plugins', __DIR__ . '/../../plugins/examples' ];
		foreach ( $paths as $path ) {
			$this->discoverFromDirectory( $path );
		}

		// Composer-installed packages
		$this->discoverFromComposer();

		// Lifecycle activation unless safe mode
		if ( ! ( $config['safe_mode'] ?? false ) ) {
			$this->initializeAndStartAll();
		} else {
			$this->logger->warning( 'Plugin safe_mode is enabled: plugins discovered but not activated.' );
		}
	}

	/**
	 * Discover plugins from a directory containing plugin subfolders.
	 *
	 * Each subdirectory is expected to contain a plugin.json manifest.
	 *
	 * @since 2.0.0
	 *
	 * @param string $baseDir Base directory path to scan.
	 * @return void
	 */
	private function discoverFromDirectory( string $baseDir ): void
	{
		if ( ! is_dir( $baseDir ) ) {
			return;
		}

		$entries = scandir( $baseDir ) ?: [];
		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}
			$pluginDir = rtrim( $baseDir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $entry;
			if ( ! is_dir( $pluginDir ) ) {
				continue;
			}
			$manifestPath = $pluginDir . DIRECTORY_SEPARATOR . 'plugin.json';
			if ( ! is_file( $manifestPath ) ) {
				continue;
			}
			$this->loadPluginFromManifest( $manifestPath, $pluginDir );
		}
	}

	/**
	 * Load and register a plugin defined by a plugin.json manifest.
	 *
	 * Validates required fields, applies allow/deny checks, includes any
	 * autoload files, resolves the entry class, builds metadata, and registers
	 * the plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param string $manifestPath Absolute path to the plugin.json file.
	 * @param string $pluginDir    Base directory of the plugin.
	 * @return void
	 */
	private function loadPluginFromManifest( string $manifestPath, string $pluginDir ): void
	{
		try {
			$json = file_get_contents( $manifestPath );
			if ( $json === false ) {
				throw new RuntimeException( 'Unable to read plugin manifest: ' . $manifestPath );
			}
			$data = json_decode( $json, true, flags: JSON_THROW_ON_ERROR );

			// Basic validation
			foreach ( [ 'id', 'name', 'version', 'entry', 'capabilities' ] as $key ) {
				if ( ! array_key_exists( $key, $data ) ) {
					throw new InvalidArgumentException( "plugin.json missing required field: {$key}" );
				}
			}

			// Allow/Deny
			$cfg       = $this->context->getConfig( 'plugins', [] );
			$allowlist = $cfg['allowlist'] ?? [];
			$denylist  = $cfg['denylist'] ?? [];
			$id        = (string) $data['id'];
			if ( ! empty( $allowlist ) && ! in_array( $id, $allowlist, true ) ) {
				$this->logger->info( "Skipping plugin {$id} not in allowlist" );
				return;
			}
			if ( in_array( $id, $denylist, true ) ) {
				$this->logger->warning( "Skipping plugin {$id} due to denylist" );
				return;
			}

			// Autoload files if provided
			$autoloadFiles = $data['autoload']['files'] ?? [];
			foreach ( $autoloadFiles as $file ) {
				$path = $pluginDir . DIRECTORY_SEPARATOR . $file;
				if ( is_file( $path ) ) {
					require_once $path;
				} else {
					$this->logger->warning( 'Autoload file not found for plugin {id}: ' . $path, [ 'id' => $id ] );
				}
			}

			$entryClass = $data['entry'];
			if ( ! class_exists( $entryClass ) ) {
				// Try namespacing relative to plugin dir via include if a file matches entry
				$candidate = $pluginDir . DIRECTORY_SEPARATOR . $entryClass . '.php';
				if ( is_file( $candidate ) ) {
					require_once $candidate;
				}
			}
			if ( ! class_exists( $entryClass ) ) {
				throw new RuntimeException( "Plugin entry class not found: {$entryClass}" );
			}

			$plugin = new $entryClass();
			if ( ! $plugin instanceof PluginInterface ) {
				throw new RuntimeException( "Plugin entry does not implement PluginInterface: {$entryClass}" );
			}

			// Metadata
			$metaInput = [
				'id'             => $data['id'],
				'name'           => $data['name'],
				'version'        => $data['version'],
				'description'    => $data['description'] ?? null,
				'author'         => $data['author'] ?? null,
				'license'        => $data['license'] ?? null,
				'homepage'       => $data['homepage'] ?? null,
				'capabilities'   => $data['capabilities'] ?? [],
				'requires'       => $data['requires'] ?? [],
				'compatibleWith' => $data['compatibleWith'] ?? null,
				'permissions'    => $data['permissions'] ?? null,
			];
			$metadata  = PluginMetadata::fromArray( $metaInput );

			// Sanity check plugin's own metadata if provided
			try {
				$pluginMeta = $plugin->getMetadata();
				if ( $pluginMeta->id !== $metadata->id ) {
					$this->logger->warning( 'Manifest/Plugin metadata id mismatch', [ 'manifest' => $metadata->id, 'plugin' => $pluginMeta->id ] );
				}
			} catch ( Throwable $e ) {
				// If plugin throws, fallback to manifest metadata
				if ( method_exists( $plugin, 'setMetadata' ) ) {
					$plugin->setMetadata( $metadata );
				}
			}

			$this->registerPlugin( $plugin, $metadata );
		} catch ( Throwable $e ) {
			$this->logger->error( 'Failed to load plugin from manifest: ' . $manifestPath . ' error: ' . $e->getMessage(), [ 'exception' => $e ] );
		}
	}

	/**
	 * Register a plugin and index it by capabilities.
	 *
	 * Adds the plugin to the registry and sorts it into capability-specific
	 * collections for efficient lookup.
	 *
	 * @since 2.0.0
	 *
	 * @param PluginInterface $plugin   The plugin instance to register.
	 * @param PluginMetadata  $metadata The plugin metadata declared by the manifest or plugin.
	 * @return void
	 */
	private function registerPlugin( PluginInterface $plugin, PluginMetadata $metadata ): void
	{
		$this->plugins[ $metadata->id ] = $plugin;

		foreach ( $metadata->capabilities as $capability ) {
			switch ( $capability ) {
				case Capability::COLOR_FORMAT:
					if ( $plugin instanceof ColorFormatPluginInterface ) {
						$this->colorFormatPlugins[] = $plugin;
					} else {
						$this->logger->warning( 'Plugin declares color_format capability but does not implement ColorFormatPluginInterface', [ 'id' => $metadata->id ] );
					}
					break;
				case Capability::ACCESSIBILITY_RULE:
					if ( $plugin instanceof AccessibilityRulePluginInterface ) {
						$this->rulePlugins[] = $plugin;
					} else {
						$this->logger->warning( 'Plugin declares accessibility_rule capability but does not implement AccessibilityRulePluginInterface', [ 'id' => $metadata->id ] );
					}
					break;
				case Capability::ANALYSIS_TOOL:
					if ( $plugin instanceof AnalysisToolPluginInterface ) {
						$this->analysisPlugins[] = $plugin;
					} else {
						$this->logger->warning( 'Plugin declares analysis_tool capability but does not implement AnalysisToolPluginInterface', [ 'id' => $metadata->id ] );
					}
					break;
			}
		}
	}

	/**
	 * Discover plugins declared by Composer packages.
	 *
	 * Scans the vendor directory for packages of type `artisanpack-ui-plugin` and
	 * registers any plugin entry classes declared in their `extra.accessibility.plugins`.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function discoverFromComposer(): void
	{
		// Best-effort discovery by scanning vendor packages' composer.json
		$vendorDir = dirname( __DIR__, 2 ) . DIRECTORY_SEPARATOR . 'vendor';
		if ( ! is_dir( $vendorDir ) ) {
			return;
		}
		$vendors = scandir( $vendorDir ) ?: [];
		foreach ( $vendors as $vendor ) {
			if ( $vendor === '.' || $vendor === '..' ) {
				continue;
			}
			$vendorPath = $vendorDir . DIRECTORY_SEPARATOR . $vendor;
			if ( ! is_dir( $vendorPath ) ) {
				continue;
			}
			$packages = scandir( $vendorPath ) ?: [];
			foreach ( $packages as $package ) {
				if ( $package === '.' || $package === '..' ) {
					continue;
				}
				$packagePath  = $vendorPath . DIRECTORY_SEPARATOR . $package;
				$composerJson = $packagePath . DIRECTORY_SEPARATOR . 'composer.json';
				if ( ! is_file( $composerJson ) ) {
					continue;
				}
				$json = @file_get_contents( $composerJson );
				if ( $json === false ) {
					continue;
				}
				$data = json_decode( $json, true );
				if ( ! is_array( $data ) ) {
					continue;
				}
				if ( ( $data['type'] ?? '' ) !== 'artisanpack-ui-plugin' ) {
					continue;
				}
				$extras = $data['extra']['accessibility']['plugins'] ?? [];
				foreach ( $extras as $entryClass ) {
					try {
						if ( ! class_exists( $entryClass ) ) {
							// Attempt PSR-4 autoload already set by Composer; if not available, skip
							continue;
						}
						$plugin = new $entryClass();
						if ( ! $plugin instanceof PluginInterface ) {
							continue;
						}

						// Try to get metadata from the plugin; if it fails, synthesize minimal metadata
						try {
							$metadata = $plugin->getMetadata();
						} catch ( Throwable $e ) {
							$name     = $data['name'] ?? $entryClass;
							$version  = $data['version'] ?? '0.0.0';
							$metadata = new PluginMetadata( id: $name, name: $name, version: $version, capabilities: [ Capability::COLOR_FORMAT ] );
						}
						$cfg       = $this->context->getConfig( 'plugins', [] );
						$allowlist = $cfg['allowlist'] ?? [];
						$denylist  = $cfg['denylist'] ?? [];
						$id        = $metadata->id;
						if ( ! empty( $allowlist ) && ! in_array( $id, $allowlist, true ) ) {
							$this->logger->info( "Skipping composer plugin {$id} not in allowlist" );
							continue;
						}
						if ( in_array( $id, $denylist, true ) ) {
							$this->logger->warning( "Skipping composer plugin {$id} due to denylist" );
							continue;
						}

						$this->registerPlugin( $plugin, $metadata );
					} catch ( Throwable $e ) {
						$this->logger->error( 'Error loading composer plugin ' . $entryClass . ': ' . $e->getMessage(), [ 'exception' => $e ] );
					}
				}
			}
		}
	}

	/**
	 * Initialize and start all registered plugins.
	 *
	 * Iterates through the registry and calls initialize and start, logging any
	 * failures without interrupting other plugins.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function initializeAndStartAll(): void
	{
		foreach ( $this->plugins as $plugin ) {
			try {
				$plugin->initialize( $this->context );
				$plugin->start();
			} catch ( Throwable $e ) {
				$this->logger->error( 'Failed to activate plugin {id}: ' . $e->getMessage(), [ 'exception' => $e ] );
			}
		}
	}

	/**
	 * Stop and destroy all registered plugins.
	 *
	 * Calls stop and destroy on each plugin, logging failures without halting
	 * the process for other plugins.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function stopAndDestroyAll(): void
	{
		foreach ( $this->plugins as $plugin ) {
			try {
				$plugin->stop();
				$plugin->destroy();
			} catch ( Throwable $e ) {
				$this->logger->error( 'Failed to deactivate plugin: ' . $e->getMessage(), [ 'exception' => $e ] );
			}
		}
	}

	/**
	 * Get all registered plugins keyed by id.
	 *
	 * @since 2.0.0
	 *
	 * @return array<string,PluginInterface> Map of plugin id to plugin instance.
	 */
	public function getPlugins(): array
	{
		return $this->plugins;
	}

	/**
	 * Get the first ColorFormat plugin that supports a given format.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format Format name to look for.
	 * @return ColorFormatPluginInterface|null Matching plugin or null if none found.
	 */
	public function getColorFormatPluginFor( string $format ): ?ColorFormatPluginInterface
	{
		foreach ( $this->colorFormatPlugins as $plugin ) {
			if ( in_array( $format, $plugin->getSupportedFormats(), true ) ) {
				return $plugin;
			}
		}
		return null;
	}

	/**
	 * Get all registered ColorFormat plugins.
	 *
	 * @since 2.0.0
	 *
	 * @return ColorFormatPluginInterface[] List of color format plugins.
	 */
	public function getColorFormatPlugins(): array
	{
		return $this->colorFormatPlugins;
	}

	/**
	 * Get all registered Accessibility Rule plugins.
	 *
	 * @since 2.0.0
	 *
	 * @return AccessibilityRulePluginInterface[] List of rule plugins.
	 */
	public function getRulePlugins(): array
	{
		return $this->rulePlugins;
	}

	/**
	 * Get all registered Analysis Tool plugins.
	 *
	 * @since 2.0.0
	 *
	 * @return AnalysisToolPluginInterface[] List of analysis tool plugins.
	 */
	public function getAnalysisPlugins(): array
	{
		return $this->analysisPlugins;
	}
}
