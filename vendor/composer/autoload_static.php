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
        'PrintMyBlog\\controllers\\Admin' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Admin.php',
        'PrintMyBlog\\controllers\\Ajax' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Ajax.php',
        'PrintMyBlog\\controllers\\Common' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Common.php',
        'PrintMyBlog\\controllers\\Frontend' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Frontend.php',
        'PrintMyBlog\\controllers\\GutenbergBlock' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/GutenbergBlock.php',
        'PrintMyBlog\\controllers\\LegacyPrintPage' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/LegacyPrintPage.php',
        'PrintMyBlog\\controllers\\LoadingPage' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/LoadingPage.php',
        'PrintMyBlog\\controllers\\Shortcodes' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/Shortcodes.php',
        'PrintMyBlog\\controllers\\helpers\\ProjectsListTable' => __DIR__ . '/../..' . '/src/PrintMyBlog/controllers/helpers/ProjectsListTable.php',
        'PrintMyBlog\\db\\PostFetcher' => __DIR__ . '/../..' . '/src/PrintMyBlog/db/PostFetcher.php',
        'PrintMyBlog\\db\\TableManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/db/TableManager.php',
        'PrintMyBlog\\domain\\DefaultDesignTemplates' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/DefaultDesignTemplates.php',
        'PrintMyBlog\\domain\\DefaultDesigns' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/DefaultDesigns.php',
        'PrintMyBlog\\domain\\DefaultFileFormats' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/DefaultFileFormats.php',
        'PrintMyBlog\\domain\\DefaultProjectContents' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/DefaultProjectContents.php',
        'PrintMyBlog\\domain\\FrontendPrintSettings' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/FrontendPrintSettings.php',
        'PrintMyBlog\\domain\\PrintButtons' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/PrintButtons.php',
        'PrintMyBlog\\domain\\PrintOptions' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/PrintOptions.php',
        'PrintMyBlog\\domain\\ProNotification' => __DIR__ . '/../..' . '/src/PrintMyBlog/domain/ProNotification.php',
        'PrintMyBlog\\entities\\DesignTemplate' => __DIR__ . '/../..' . '/src/PrintMyBlog/entities/DesignTemplate.php',
        'PrintMyBlog\\entities\\FileFormat' => __DIR__ . '/../..' . '/src/PrintMyBlog/entities/FileFormat.php',
        'PrintMyBlog\\entities\\ProjectGeneration' => __DIR__ . '/../..' . '/src/PrintMyBlog/entities/ProjectGeneration.php',
        'PrintMyBlog\\exceptions\\TemplateDoesNotExist' => __DIR__ . '/../..' . '/src/PrintMyBlog/exceptions/TemplateDoesNotExist.php',
        'PrintMyBlog\\factories\\FileFormatFactory' => __DIR__ . '/../..' . '/src/PrintMyBlog/factories/FileFormatFactory.php',
        'PrintMyBlog\\factories\\ProjectFileGeneratorFactory' => __DIR__ . '/../..' . '/src/PrintMyBlog/factories/ProjectFileGeneratorFactory.php',
        'PrintMyBlog\\factories\\ProjectGenerationFactory' => __DIR__ . '/../..' . '/src/PrintMyBlog/factories/ProjectGenerationFactory.php',
        'PrintMyBlog\\helpers\\ArgMagician' => __DIR__ . '/../..' . '/src/PrintMyBlog/helpers/ArgMagician.php',
        'PrintMyBlog\\orm\\entities\\Design' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/entities/Design.php',
        'PrintMyBlog\\orm\\entities\\Project' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/entities/Project.php',
        'PrintMyBlog\\orm\\entities\\ProjectSection' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/entities/ProjectSection.php',
        'PrintMyBlog\\orm\\managers\\DesignManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/managers/DesignManager.php',
        'PrintMyBlog\\orm\\managers\\ProjectManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/managers/ProjectManager.php',
        'PrintMyBlog\\orm\\managers\\ProjectSectionManager' => __DIR__ . '/../..' . '/src/PrintMyBlog/orm/managers/ProjectSectionManager.php',
        'PrintMyBlog\\services\\DesignRegistry' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/DesignRegistry.php',
        'PrintMyBlog\\services\\DesignTemplateRegistry' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/DesignTemplateRegistry.php',
        'PrintMyBlog\\services\\FileFormatRegistry' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/FileFormatRegistry.php',
        'PrintMyBlog\\services\\config\\Config' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/config/Config.php',
        'PrintMyBlog\\services\\generators\\PdfGenerator' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/generators/PdfGenerator.php',
        'PrintMyBlog\\services\\generators\\ProjectFileGeneratorBase' => __DIR__ . '/../..' . '/src/PrintMyBlog/services/generators/ProjectFileGeneratorBase.php',
        'PrintMyBlog\\system\\Activation' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Activation.php',
        'PrintMyBlog\\system\\Capabilities' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Capabilities.php',
        'PrintMyBlog\\system\\Context' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Context.php',
        'PrintMyBlog\\system\\CustomPostTypes' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/CustomPostTypes.php',
        'PrintMyBlog\\system\\Init' => __DIR__ . '/../..' . '/src/PrintMyBlog/system/Init.php',
        'Twine\\admin\\news\\DashboardNews' => __DIR__ . '/../..' . '/src/Twine/admin/news/DashboardNews.php',
        'Twine\\compatibility\\CompatibilityBase' => __DIR__ . '/../..' . '/src/Twine/compatibility/CompatibilityBase.php',
        'Twine\\controllers\\BaseController' => __DIR__ . '/../..' . '/src/Twine/controllers/BaseController.php',
        'Twine\\forms\\base\\FormSectionBase' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionBase.php',
        'Twine\\forms\\base\\FormSectionDetails' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionDetails.php',
        'Twine\\forms\\base\\FormSectionHtml' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionHtml.php',
        'Twine\\forms\\base\\FormSectionHtmlFromTemplate' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionHtmlFromTemplate.php',
        'Twine\\forms\\base\\FormSectionProper' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionProper.php',
        'Twine\\forms\\base\\FormSectionValidatable' => __DIR__ . '/../..' . '/src/Twine/forms/base/FormSectionValidatable.php',
        'Twine\\forms\\helpers\\ImproperUsageException' => __DIR__ . '/../..' . '/src/Twine/forms/helpers/ImproperUsageException.php',
        'Twine\\forms\\helpers\\ValidationError' => __DIR__ . '/../..' . '/src/Twine/forms/helpers/ValidationError.php',
        'Twine\\forms\\inputs\\AdminFileUploaderInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/AdminFileUploaderInput.php',
        'Twine\\forms\\inputs\\ButtonInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/ButtonInput.php',
        'Twine\\forms\\inputs\\CheckboxMultiInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/CheckboxMultiInput.php',
        'Twine\\forms\\inputs\\DatepickerInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/DatepickerInput.php',
        'Twine\\forms\\inputs\\EmailConfirmInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/EmailConfirmInput.php',
        'Twine\\forms\\inputs\\EmailInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/EmailInput.php',
        'Twine\\forms\\inputs\\FixedHiddenInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/FixedHiddenInput.php',
        'Twine\\forms\\inputs\\FloatInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/FloatInput.php',
        'Twine\\forms\\inputs\\FormInputBase' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/FormInputBase.php',
        'Twine\\forms\\inputs\\FormInputWithOptionsBase' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/FormInputWithOptionsBase.php',
        'Twine\\forms\\inputs\\HiddenInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/HiddenInput.php',
        'Twine\\forms\\inputs\\IntegerInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/IntegerInput.php',
        'Twine\\forms\\inputs\\MonthInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/MonthInput.php',
        'Twine\\forms\\inputs\\PasswordInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/PasswordInput.php',
        'Twine\\forms\\inputs\\PhoneInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/PhoneInput.php',
        'Twine\\forms\\inputs\\RadioButtonInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/RadioButtonInput.php',
        'Twine\\forms\\inputs\\SelectInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/SelectInput.php',
        'Twine\\forms\\inputs\\SelectMultipleInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/SelectMultipleInput.php',
        'Twine\\forms\\inputs\\SelectRevealInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/SelectRevealInput.php',
        'Twine\\forms\\inputs\\SubmitInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/SubmitInput.php',
        'Twine\\forms\\inputs\\TextAreaInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/TextAreaInput.php',
        'Twine\\forms\\inputs\\TextInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/TextInput.php',
        'Twine\\forms\\inputs\\YearInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/YearInput.php',
        'Twine\\forms\\inputs\\YesNoInput' => __DIR__ . '/../..' . '/src/Twine/forms/inputs/YesNoInput.php',
        'Twine\\forms\\strategies\\FormInputStrategyBase' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/FormInputStrategyBase.php',
        'Twine\\forms\\strategies\\display\\AdminFileUploaderDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/AdminFileUploaderDisplay.php',
        'Twine\\forms\\strategies\\display\\ButtonDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/ButtonDisplay.php',
        'Twine\\forms\\strategies\\display\\CheckboxDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/CheckboxDisplay.php',
        'Twine\\forms\\strategies\\display\\CompoundInputDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/CompoundInputDisplay.php',
        'Twine\\forms\\strategies\\display\\DatepickerDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/DatepickerDisplay.php',
        'Twine\\forms\\strategies\\display\\DisplayBase' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/DisplayBase.php',
        'Twine\\forms\\strategies\\display\\HiddenDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/HiddenDisplay.php',
        'Twine\\forms\\strategies\\display\\NumberInputDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/NumberInputDisplay.php',
        'Twine\\forms\\strategies\\display\\RadioButtonDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/RadioButtonDisplay.php',
        'Twine\\forms\\strategies\\display\\Select2Display' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/Select2DisplayStrategy.php',
        'Twine\\forms\\strategies\\display\\SelectDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/SelectDisplay.php',
        'Twine\\forms\\strategies\\display\\SelectMultipleDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/SelectMultipleDisplay.php',
        'Twine\\forms\\strategies\\display\\SingleCheckboxDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/SingleCheckboxDisplay.php',
        'Twine\\forms\\strategies\\display\\SubmitInputDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/SubmitInputDisplay.php',
        'Twine\\forms\\strategies\\display\\TextAreaDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/TextAreaDisplay.php',
        'Twine\\forms\\strategies\\display\\TextInputDisplay' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/display/TextInputDisplay.php',
        'Twine\\forms\\strategies\\layout\\AdminOneColumnLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/AdminOneColumnLayout.php',
        'Twine\\forms\\strategies\\layout\\AdminTwoColumnLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/AdminTwoColumnLayout.php',
        'Twine\\forms\\strategies\\layout\\DetailsSummaryLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/DetailsSummaryLayout.php',
        'Twine\\forms\\strategies\\layout\\DivPerSectionLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/DivPerSectionLayout.php',
        'Twine\\forms\\strategies\\layout\\FieldsetSectionLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/FieldsetSectionLayout.php',
        'Twine\\forms\\strategies\\layout\\FormSectionLayoutBase' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/FormSectionLayoutBase.php',
        'Twine\\forms\\strategies\\layout\\NoLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/NoLayout.php',
        'Twine\\forms\\strategies\\layout\\TemplateLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/TemplateLayout.php',
        'Twine\\forms\\strategies\\layout\\TwoColumnLayout' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/layout/TwoColumnLayout.php',
        'Twine\\forms\\strategies\\normalization\\AllCapsNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/AllCapsNormalization.php',
        'Twine\\forms\\strategies\\normalization\\BooleanNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/BooleanNormalization.php',
        'Twine\\forms\\strategies\\normalization\\FileNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/FileNormalization.php',
        'Twine\\forms\\strategies\\normalization\\FloatNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/FloatNormalization.php',
        'Twine\\forms\\strategies\\normalization\\IntNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/IntNormalization.php',
        'Twine\\forms\\strategies\\normalization\\ManyValuedNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/ManyValuedNormalization.php',
        'Twine\\forms\\strategies\\normalization\\NormalizationBase' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/NormalizationBase.php',
        'Twine\\forms\\strategies\\normalization\\NullNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/NullNormalization.php',
        'Twine\\forms\\strategies\\normalization\\SlugNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/SlugNormalization.php',
        'Twine\\forms\\strategies\\normalization\\TextNormalization' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/normalization/TextNormalization.php',
        'Twine\\forms\\strategies\\validation\\ConditionallyRequiredValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/ConditionallyRequiredValidation.php',
        'Twine\\forms\\strategies\\validation\\EmailValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/EmailValidation.php',
        'Twine\\forms\\strategies\\validation\\EnumValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/EnumValidation.php',
        'Twine\\forms\\strategies\\validation\\EqualToValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/EqualToValidation.php',
        'Twine\\forms\\strategies\\validation\\FloatValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/FloatValidation.php',
        'Twine\\forms\\strategies\\validation\\FullHtmlValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/FullHtmlValidation.php',
        'Twine\\forms\\strategies\\validation\\IntValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/IntValidation.php',
        'Twine\\forms\\strategies\\validation\\ManyValuedValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/ManyValuedValidation.php',
        'Twine\\forms\\strategies\\validation\\MaxLengthValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/MaxLengthValidation.php',
        'Twine\\forms\\strategies\\validation\\MinLengthValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/MinLengthValidation.php',
        'Twine\\forms\\strategies\\validation\\PlaintextValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/PlaintextValidation.php',
        'Twine\\forms\\strategies\\validation\\RequiredValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/RequiredValidation.php',
        'Twine\\forms\\strategies\\validation\\TextValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/TextValidation.php',
        'Twine\\forms\\strategies\\validation\\UrlValidation' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/UrlValidation.php',
        'Twine\\forms\\strategies\\validation\\ValidationBase' => __DIR__ . '/../..' . '/src/Twine/forms/strategies/validation/ValidationBase.php',
        'Twine\\helpers\\Array2' => __DIR__ . '/../..' . '/src/Twine/helpers/Array2.php',
        'Twine\\helpers\\Html' => __DIR__ . '/../..' . '/src/Twine/helpers/Html.php',
        'Twine\\orm\\entities\\PostWrapper' => __DIR__ . '/../..' . '/src/Twine/orm/entities/PostWrapper.php',
        'Twine\\orm\\managers\\PostWrapperManager' => __DIR__ . '/../..' . '/src/Twine/orm/managers/PostWrapperManager.php',
        'Twine\\services\\config\\Config' => __DIR__ . '/../..' . '/src/Twine/services/config/Config.php',
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
