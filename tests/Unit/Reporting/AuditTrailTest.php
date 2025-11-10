<?php

namespace ArtisanPack\Accessibility\Tests\Unit\Reporting;

use ArtisanPack\Accessibility\Events\ColorContrastChecked;
use ArtisanPack\Accessibility\Listeners\LogColorContrastCheck;
use ArtisanPack\Accessibility\Models\AuditTrail;
use ArtisanPack\Accessibility\Reporting\AuditTrail as AuditTrailService;
use Tests\TestCase;
use Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class AuditTrailTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function it_logs_an_event()
	{
		$user = User::factory()->create();
		$this->actingAs( $user );

		$auditTrail = new AuditTrailService();
		$auditTrail->log( 'test_action', [ 'foo' => 'bar' ] );

		$this->assertDatabaseHas( 'audit_trails', [
			'user_id' => $user->id,
			'action'  => 'test_action',
			'details' => json_encode( [ 'foo' => 'bar' ] ),
		] );
	}

	/** @test */
	public function it_listens_for_the_color_contrast_checked_event()
	{
		$user = User::factory()->create();
		$this->actingAs( $user );

		$event = new ColorContrastChecked( '#ffffff', '#000000', 'AA', false, true );
		event( $event );

		$this->assertDatabaseHas( 'audit_trails', [
			'user_id' => $user->id,
			'action'  => 'color_contrast_checked',
		] );
	}
}
