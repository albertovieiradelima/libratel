<?php

// site routes
$app->mount('/', new App\Controller\IndexController());
$app->mount('/site/evento',new \App\Controller\EventInscriptionController());

// admin routes
$app->mount('/admin', new App\Controller\AdminController());
$app->mount('/admin', new App\Controller\UserController());
$app->mount('/admin', new App\Controller\UserGroupController());
$app->mount('/admin', new App\Controller\CourseController());
$app->mount('/admin', new App\Controller\EventController());
$app->mount('/admin', new App\Controller\FeedController());
$app->mount('/admin', new App\Controller\SpacePartnerController());
$app->mount('/admin', new App\Controller\AssociateController());
$app->mount('/admin', new App\Controller\NewsletterController());
$app->mount('/admin', new App\Controller\AboutusController());
$app->mount('/admin', new App\Controller\ContactusController());
$app->mount('/admin', new App\Controller\StoreController());
$app->mount('/admin', new App\Controller\StoreCategoryController());
$app->mount('/admin', new App\Controller\MagazineController());
$app->mount('/admin', new App\Controller\ClientController());
$app->mount('/admin', new App\Controller\TopImageController());
$app->mount('/admin', new App\Controller\BannerController());
$app->mount('/admin', new App\Controller\BackGroundController());
$app->mount('/admin', new App\Controller\SubscriberController());
$app->mount('/admin', new App\Controller\InaugurationController());
$app->mount('/admin', new App\Controller\InaugurationCategoryController());
$app->mount('/admin', new App\Controller\SetupController());
$app->mount('/admin', new App\Controller\EventChargePeriodController());
$app->mount('/admin', new App\Controller\EventDiscountCouponController());
$app->mount('/admin', new App\Controller\EventRegistrationController());
$app->mount('/admin', new App\Controller\EventRegistrationParticipantsController());
$app->mount('/admin', new App\Controller\EventReportTrackingController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\AwardController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\AwardFieldController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\EventController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\SponsorController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\SponsorCategoryController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\EventSponsorController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\AwardEventController());
$app->mount('/admin/abrasce-award', new App\Controller\AbrasceAward\RegistrationController());
$app->mount('/admin/restricted-area', new App\Controller\RestrictedArea\FileCategoryController());

// crmall routes
$app->mount('/crmall', new App\Controller\CrmallController());