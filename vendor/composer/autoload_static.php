<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaa3acace4531482e132095eacb9548bb
{
    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mnelson4\\' => 9,
        ),
        'T' => 
        array (
            'Twine\\' => 6,
        ),
        'P' => 
        array (
            'PrintMyBlog\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mnelson4\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/mnelson4',
        ),
        'Twine\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Twine',
        ),
        'PrintMyBlog\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/PrintMyBlog',
        ),
    );

    public static $classMap = array (
        'PrintMyBlog\\compatibility\\DetectAndActivate' => __DIR__ . '/../..' . '/src/PrintMyBlog/compatibility/DetectAndActivate.php',
        'PrintMyBlog\\compatibility\\plugins\\EasyFootnotes' => __DIR__ . '/../..' . '/src/PrintMyBlog/compatibility/plugins/EasyFootnotes.php',
        'PrintMyBlog\\compatibility\\plugins\\LazyLoadingFeaturePlugin' => __DIR__ . '/../..' . '/src/PrintMyBlog/compatibility/plugins/LazyLoadingFeaturePlugin.php',
        'PrintMyBlog\\controllers\\Ajax' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Ajax.php',
        'PrintMyBlog\\controllers\\LoadingPage' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/LoadingPage.php',
        'PrintMyBlog\\controllers\\PmbActivation' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbActivation.php',
        'PrintMyBlog\\controllers\\PmbAdmin' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbAdmin.php',
        'PrintMyBlog\\controllers\\PmbCommon' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbCommon.php',
        'PrintMyBlog\\controllers\\PmbFrontend' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbFrontend.php',
        'PrintMyBlog\\controllers\\PmbGutenbergBlock' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbGutenbergBlock.php',
        'PrintMyBlog\\controllers\\PmbInit' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbInit.php',
        'PrintMyBlog\\controllers\\PmbPrintPage' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/PmbPrintPage.php',
        'PrintMyBlog\\controllers\\Shortcodes' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Shortcodes.php',
        'PrintMyBlog\\controllers\\helpers\\ProjectsListTable' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/helpers/ProjectsListTable.php',
        'PrintMyBlog\\db\\PartFetcher' => __DIR__ . '/../..' . '/src/PrintMyBlog/db/PartFetcher.php',
        'PrintMyBlog\\db\\PostFetcher' => __DIR__ . '/../..' . '/src/PrintMyBlog/db/PostFetcher.php',
        'PrintMyBlog\\db\\TableManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/db/TableManager.php',
        'PrintMyBlog\\domain\\FormatManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/FormatManager.php',
        'PrintMyBlog\\domain\\FrontendPrintSettings' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/FrontendPrintSettings.php',
        'PrintMyBlog\\domain\\PrintButtons' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/PrintButtons.php',
        'PrintMyBlog\\domain\\PrintOptions' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/PrintOptions.php',
        'PrintMyBlog\\domain\\ProNotification' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/ProNotification.php',
        'PrintMyBlog\\entities\\Format' => __DIR__ . '/../..' . '/src/PrintMyBlog/entities/Format.php',
        'PrintMyBlog\\orm\\entities\\Design' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/entities/Design.php',
        'PrintMyBlog\\orm\\entities\\Project' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/entities/Project.php',
        'PrintMyBlog\\orm\\managers\\DesignManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/managers/DesignManager.php',
        'PrintMyBlog\\orm\\managers\\ProjectManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/managers/ProjectManager.php',
        'PrintMyBlog\\services\\ProjectHtmlGenerator' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/ProjectHtmlGenerator.php',
        'PrintMyBlog\\system\\Activation' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Activation.php',
        'PrintMyBlog\\system\\Capabilities' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Capabilities.php',
        'PrintMyBlog\\system\\Context' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Context.php',
        'PrintMyBlog\\system\\CustomPostTypes' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/CustomPostTypes.php',
        'PrintMyBlog\\system\\Init' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Init.php',
        'Twine\\admin\\news\\DashboardNews' => __DIR__ . '/../..' . '/src/Twine/admin/news/DashboardNews.php',
        'Twine\\compatibility\\CompatibilityBase' => __DIR__ . '/../..' . '/src/Twine/compatibility/CompatibilityBase.php',
        'Twine\\controllers\\BaseController' => __DIR__ . '/../..' . '/src/Twine/controllers/BaseController.php',
        'Twine\\services\\display\\FormInputs' => __DIR__ . '/../..' . '/src/Twine/services/display/FormInputs.php',
        'Twine\\services\\filesystem\\File' => __DIR__ . '/../..' . '/src/Twine/services/filesystem/File.php',
        'Twine\\services\\filesystem\\Folder' => __DIR__ . '/../..' . '/src/Twine/services/filesystem/Folder.php',
        'Twine\\services\\filesystem\\ThingOnServer' => __DIR__ . '/../..' . '/src/Twine/services/filesystem/ThingOnServer.php',
        'Twine\\system\\Activation' => __DIR__ . '/../..' . '/src/Twine/system/Activation.php',
        'Twine\\system\\Context' => __DIR__ . '/../..' . '/src/Twine/system/Context.php',
        'Twine\\system\\RequestType' => __DIR__ . '/../..' . '/src/Twine/system/RequestType.php',
        'Twine\\system\\VersionHistory' => __DIR__ . '/../..' . '/src/Twine/system/VersionHistory.php',
        'mnelson4\\RestApiDetector\\RestApiDetector' => __DIR__ . '/../..' . '/src/mnelson4/RestApiDetector.php',
        'mnelson4\\RestApiDetector\\RestApiDetectorError' => __DIR__ . '/../..' . '/src/mnelson4/RestApiDetectorError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaa3acace4531482e132095eacb9548bb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaa3acace4531482e132095eacb9548bb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitaa3acace4531482e132095eacb9548bb::$classMap;

        }, null, ClassLoader::class);
    }
}
