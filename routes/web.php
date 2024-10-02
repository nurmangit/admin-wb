<?php

use App\Http\Controllers\apps\ApprovalController;
use App\Http\Controllers\apps\Area;
use App\Http\Controllers\apps\AreaController;
use App\Http\Controllers\apps\Region;
use App\Http\Controllers\apps\RegionController;
use App\Http\Controllers\apps\Transporter;
use App\Http\Controllers\apps\TransporterController;
use App\Http\Controllers\apps\TransporterRateController;
use App\Http\Controllers\apps\VehicleController;
use App\Http\Controllers\apps\VehicleType;
use App\Http\Controllers\apps\VehicleTypeController;
use App\Http\Controllers\apps\WeightBridgeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Main Page Route
Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

// Master Data Route
Route::prefix('master-data')->name('master-data.')->group(function () {
    Route::prefix('/vehicle')->name('vehicle.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('list');
        Route::get('/view', [VehicleController::class, 'index'])->name('view');
        Route::get('/create', [VehicleController::class, 'create'])->name('create');
        Route::post('/store', [VehicleController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [VehicleController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [VehicleController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [VehicleController::class, 'update'])->name('update');

        Route::get('/get-vehicle-details', [VehicleController::class, 'getVehicleDetails'])->name('details');
    });
    Route::prefix('/vehicle-type')->name('vehicle-type.')->group(function () {
        Route::get('/', [VehicleTypeController::class, 'index'])->name('list');
        Route::get('/view', [VehicleTypeController::class, 'index'])->name('view');
        Route::get('/create', [VehicleTypeController::class, 'create'])->name('create');
        Route::post('/store', [VehicleTypeController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [VehicleTypeController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [VehicleTypeController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [VehicleTypeController::class, 'update'])->name('update');
    });
    Route::prefix('/transporter')->name('transporter.')->group(function () {
        Route::get('/', [TransporterController::class, 'index'])->name('list');
        Route::get('/view', [TransporterController::class, 'index'])->name('view');
        Route::get('/create', [TransporterController::class, 'create'])->name('create');
        Route::post('/store', [TransporterController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [TransporterController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [TransporterController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [TransporterController::class, 'update'])->name('update');
    });
    Route::prefix('/transporter-rate')->name('transporter-rate.')->group(function () {
        Route::get('/', [TransporterRateController::class, 'index'])->name('list');
        Route::get('/view', [TransporterRateController::class, 'index'])->name('view');
        Route::get('/create', [TransporterRateController::class, 'create'])->name('create');
        Route::post('/store', [TransporterRateController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [TransporterRateController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [TransporterRateController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [TransporterRateController::class, 'update'])->name('update');
    });
    Route::prefix('/area')->name('area.')->group(function () {
        Route::get('/', [AreaController::class, 'index'])->name('list');
        Route::get('/view', [AreaController::class, 'index'])->name('view');
        Route::get('/create', [AreaController::class, 'create'])->name('create');
        Route::post('/store', [AreaController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [AreaController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [AreaController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [AreaController::class, 'update'])->name('update');
    });
    Route::prefix('/region')->name('region.')->group(function () {
        Route::get('/', [RegionController::class, 'index'])->name('list');
        Route::get('/view', [RegionController::class, 'index'])->name('view');
        Route::get('/create', [RegionController::class, 'create'])->name('create');
        Route::post('/store', [RegionController::class, 'store'])->name('store');
        Route::delete('/delete/{uuid}', [RegionController::class, 'delete'])->name('delete');
        Route::get('/edit/{uuid}', [RegionController::class, 'edit'])->name('edit');
        Route::post('/update/{uuid}', [RegionController::class, 'update'])->name('update');
    });
});
Route::prefix('transaction')->name('transaction.')->group(function () {
    Route::prefix('/weight-bridge')->name('weight-bridge.')->group(function () {
        Route::get('/data', [WeightBridgeController::class, 'index'])->name('data');
        Route::get('/view/{weightBridgeUuid}', [WeightBridgeController::class, 'view'])->name('view');
        Route::get('/receiving-material', [WeightBridgeController::class, 'receivingMaterial'])->name('receiving-material');
        Route::get('/finish-good', [WeightBridgeController::class, 'finishGood'])->name('finish-good');
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.list');
        Route::post('/approval/approve/{approvalUuid}', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/reject/{approvalUuid}', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/weight-in', [WeightBridgeController::class, 'weightIn'])->name('weightIn');
        Route::post('/weight-out', [WeightBridgeController::class, 'weightOut'])->name('weightOut');
    });
});
// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
