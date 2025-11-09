<?php

namespace ArtisanPack\Accessibility\Core\Caching;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileCache implements CacheInterface
{
	protected string $path;

	public function __construct( string $path )
	{
		$this->path = $path;
		if ( ! is_dir( $this->path ) ) {
			mkdir( $this->path, 0777, true );
		}
	}

	public function clear(): bool
	{
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->path, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			$todo = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
			$todo( $fileinfo->getRealPath() );
		}

		return true;
	}

	public function getMultiple( iterable $keys, mixed $default = null ): iterable
	{
		$result = [];
		foreach ( $keys as $key ) {
			$result[ $key ] = $this->get( $key, $default );
		}
		return $result;
	}

	public function get( string $key, mixed $default = null ): mixed
	{
		$path = $this->getFilePath( $key );
		if ( ! file_exists( $path ) ) {
			return $default;
		}

		$content = file_get_contents( $path );
		if ( $content === false ) {
			return $default;
		}

		$data = @unserialize( $content, [ 'allowed_classes' => false ] );
		if ( ! is_array( $data ) || ! array_key_exists( 'value', $data ) ) {
			$this->delete( $key );
			return $default;
		}


		if ( isset( $data['expires'] ) && time() > $data['expires'] ) {
			$this->delete( $key );
			return $default;
		}

		return $data['value'];
	}

	protected function getFilePath( string $key ): string
	{
		$hash = sha1( $key );
		return $this->path . '/' . substr( $hash, 0, 2 ) . '/' . substr( $hash, 2, 2 ) . '/' . $hash;
	}

	public function delete( string $key ): bool
	{
		$path = $this->getFilePath( $key );
		if ( file_exists( $path ) ) {
			return unlink( $path );
		}
		return true;
	}

	public function setMultiple( iterable $values, DateInterval|int|null $ttl = null ): bool
	{
		$success = true;
		foreach ( $values as $key => $value ) {
			if ( ! $this->set( $key, $value, $ttl ) ) {
				$success = false;
			}
		}
		return $success;
	}

	public function set( string $key, mixed $value, DateInterval|int|null $ttl = null ): bool
	{
		$path = $this->getFilePath( $key );
		$dir  = dirname( $path );

		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0777, true );
		}

		$data = [ 'value' => $value ];
		if ( $ttl ) {
			if ( $ttl instanceof DateInterval ) {
				$ttl = ( new DateTime() )->add( $ttl )->getTimestamp() - time();
			}
			$data['expires'] = time() + $ttl;
		}

		return file_put_contents( $path, serialize( $data ) ) !== false;
	}

	public function deleteMultiple( iterable $keys ): bool
	{
		$success = true;
		foreach ( $keys as $key ) {
			if ( ! $this->delete( $key ) ) {
				$success = false;
			}
		}
		return $success;
	}

	public function has( string $key ): bool
	{
		return $this->get( $key ) !== null;
	}
}
