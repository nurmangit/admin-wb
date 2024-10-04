<?php

use App\Http\Controllers\apps\ApprovalController;
use App\Http\Controllers\apps\Area;
use App\Http\Controllers\apps\AreaController;
use App\Http\Controllers\apps\PrintController;
use App\Http\Controllers\apps\Region;
use App\Http\Controllers\apps\RegionController;
use App\Http\Controllers\apps\Transporter;
use App\Http\Controllers\apps\TransporterController;
use App\Http\Controllers\apps\TransporterRateController;
use App\Http\Controllers\apps\VehicleController;
use App\Http\Controllers\apps\VehicleType;
use App\Http\Controllers\apps\VehicleTypeController;
use App\Http\Controllers\apps\WeightBridgeController;
use App\Http\Controllers\authentications\AuthController;
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
        route::get('/print/{uuid}/slip', [PrintController::class, 'generateSlipPDF'])->name('printSlip');
    });
});

// pages
Route::get('/account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');

// authentication
Route::get('/auth/login', [AuthController::class, 'index'])->name('login');
