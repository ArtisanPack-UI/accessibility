<?php

uses(Tests\TestCase::class);

use ArtisanPack\Accessibility\Core\A11y;
use ArtisanPack\Accessibility\Laravel\Facades\A11y as A11yFacade;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User;

it(
    'service provider is registered', function () {
        $this->assertTrue(app()->bound('a11y'));
    } 
);

it(
    'can resolve a11y from container', function () {
        $a11y = app('a11y');
        $this->assertInstanceOf(A11y::class, $a11y);
    } 
);

it(
    'facade returns correct instance', function () {
        $this->assertInstanceOf(A11y::class, A11yFacade::getFacadeRoot());
    } 
);

it(
    'a11yCSSVarBlackOrWhite facade works', function () {
        $this->assertEquals('black', A11yFacade::a11yCSSVarBlackOrWhite('#ffffff'));
        $this->assertEquals('white', A11yFacade::a11yCSSVarBlackOrWhite('#000000'));
    } 
);

it(
    'a11yGetContrastColor facade works', function () {
        $this->assertEquals('#000000', A11yFacade::a11yGetContrastColor('#ffffff'));
        $this->assertEquals('#FFFFFF', A11yFacade::a11yGetContrastColor('#000000'));
    } 
);

it(
    'a11yCheckContrastColor facade works', function () {
        $this->assertTrue(A11yFacade::a11yCheckContrastColor('#ffffff', '#000000'));
        $this->assertFalse(A11yFacade::a11yCheckContrastColor('#ffffff', '#fefefe'));
    } 
);

    test(
        'it a11yCheckContrastColor facade works again', function () {
            $this->assertTrue(A11yFacade::a11yCheckContrastColor('#000000', '#FFFFFF'));
        }
    );

    test(
        'it a11yGetContrastColor facade works again', function () {
            $this->assertEquals('#FFFFFF', A11yFacade::a11yGetContrastColor('#000000'));
        }
    );

    test(
        'it a11yCSSVarBlackOrWhite facade works again', function () {
            $this->assertEquals('white', A11yFacade::a11yCSSVarBlackOrWhite('#000000'));
        }
    );


    it(
        'a11y helper returns instance', function () {
            $this->assertInstanceOf(A11y::class, a11y());
        } 
    );

    it(
        'a11yCSSVarBlackOrWhite helper works', function () {
            $this->assertEquals('black', a11yCSSVarBlackOrWhite('#ffffff'));
            $this->assertEquals('white', a11yCSSVarBlackOrWhite('#000000'));
        } 
    );

    it(
        'a11yGetContrastColor helper works', function () {
            $this->assertEquals('#000000', a11yGetContrastColor('#ffffff'));
            $this->assertEquals('#FFFFFF', a11yGetContrastColor('#000000'));
        } 
    );

    it(
        'a11yCheckContrastColor helper works', function () {
            $this->assertTrue(a11yCheckContrastColor('#ffffff', '#000000'));
            $this->assertFalse(a11yCheckContrastColor('#ffffff', '#fefefe'));
        } 
    );

    it(
        'generateAccessibleTextColor helper works', function () {
            $this->assertEquals('#000000', generateAccessibleTextColor('#ffffff'));
        } 
    );

    it(
        'loads default configuration', function () {
            $this->assertEquals(4.5, config('artisanpack.accessibility.wcag_thresholds.aa'));
            $this->assertEquals(7.0, config('artisanpack.accessibility.wcag_thresholds.aaa'));
            $this->assertEquals('array', config('artisanpack.accessibility.cache.default'));
            $this->assertEquals(1000, config('artisanpack.accessibility.cache.stores.array.limit'));
        }
    );

    it(
        'can override configuration', function () {
            config()->set('artisanpack.accessibility.wcag_thresholds.aa', 5.0);
            config()->set('artisanpack.accessibility.cache.stores.array.limit', 500);

            $this->assertEquals(5.0, config('artisanpack.accessibility.wcag_thresholds.aa'));
            $this->assertEquals(500, config('artisanpack.accessibility.cache.stores.array.limit'));
        }
    );

