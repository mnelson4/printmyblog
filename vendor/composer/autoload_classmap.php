<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'PrintMyBlog\\compatibility\\DetectAndActivate' => $baseDir . '/src/PrintMyBlog/compatibility/DetectAndActivate.php',
    'PrintMyBlog\\compatibility\\plugins\\CoBlocks' => $baseDir . '/src/PrintMyBlog/compatibility/plugins/CoBlocks.php',
    'PrintMyBlog\\compatibility\\plugins\\EasyFootnotes' => $baseDir . '/src/PrintMyBlog/compatibility/plugins/EasyFootnotes.php',
    'PrintMyBlog\\compatibility\\plugins\\LazyLoadingFeaturePlugin' => $baseDir . '/src/PrintMyBlog/compatibility/plugins/LazyLoadingFeaturePlugin.php',
    'PrintMyBlog\\compatibility\\plugins\\TablePress' => $baseDir . '/src/PrintMyBlog/compatibility/plugins/TablePress.php',
    'PrintMyBlog\\compatibility\\plugins\\WpVrView' => $baseDir . '/src/PrintMyBlog/compatibility/plugins/WpVrView.php',
    'PrintMyBlog\\controllers\\Admin' => $baseDir . '/src/PrintMyBlog/controllers/Admin.php',
    'PrintMyBlog\\controllers\\Ajax' => $baseDir . '/src/PrintMyBlog/controllers/Ajax.php',
    'PrintMyBlog\\controllers\\Common' => $baseDir . '/src/PrintMyBlog/controllers/Common.php',
    'PrintMyBlog\\controllers\\Frontend' => $baseDir . '/src/PrintMyBlog/controllers/Frontend.php',
    'PrintMyBlog\\controllers\\GutenbergBlock' => $baseDir . '/src/PrintMyBlog/controllers/GutenbergBlock.php',
    'PrintMyBlog\\controllers\\LegacyPrintPage' => $baseDir . '/src/PrintMyBlog/controllers/LegacyPrintPage.php',
    'PrintMyBlog\\controllers\\Shortcodes' => $baseDir . '/src/PrintMyBlog/controllers/Shortcodes.php',
    'PrintMyBlog\\controllers\\helpers\\ProjectsListTable' => $baseDir . '/src/PrintMyBlog/controllers/helpers/ProjectsListTable.php',
    'PrintMyBlog\\db\\PostFetcher' => $baseDir . '/src/PrintMyBlog/db/PostFetcher.php',
    'PrintMyBlog\\db\\TableManager' => $baseDir . '/src/PrintMyBlog/db/TableManager.php',
    'PrintMyBlog\\domain\\DefaultDesignTemplates' => $baseDir . '/src/PrintMyBlog/domain/DefaultDesignTemplates.php',
    'PrintMyBlog\\domain\\DefaultDesigns' => $baseDir . '/src/PrintMyBlog/domain/DefaultDesigns.php',
    'PrintMyBlog\\domain\\DefaultFileFormats' => $baseDir . '/src/PrintMyBlog/domain/DefaultFileFormats.php',
    'PrintMyBlog\\domain\\DefaultPersistentNotices' => $baseDir . '/src/PrintMyBlog/domain/DefaultPersistentNotices.php',
    'PrintMyBlog\\domain\\DefaultProjectContents' => $baseDir . '/src/PrintMyBlog/domain/DefaultProjectContents.php',
    'PrintMyBlog\\domain\\DefaultSectionTemplates' => $baseDir . '/src/PrintMyBlog/domain/DefaultSectionTemplates.php',
    'PrintMyBlog\\domain\\FrontendPrintSettings' => $baseDir . '/src/PrintMyBlog/domain/FrontendPrintSettings.php',
    'PrintMyBlog\\domain\\PrintButtons' => $baseDir . '/src/PrintMyBlog/domain/PrintButtons.php',
    'PrintMyBlog\\domain\\PrintOptions' => $baseDir . '/src/PrintMyBlog/domain/PrintOptions.php',
    'PrintMyBlog\\domain\\ProNotification' => $baseDir . '/src/PrintMyBlog/domain/ProNotification.php',
    'PrintMyBlog\\entities\\DesignTemplate' => $baseDir . '/src/PrintMyBlog/entities/DesignTemplate.php',
    'PrintMyBlog\\entities\\FileFormat' => $baseDir . '/src/PrintMyBlog/entities/FileFormat.php',
    'PrintMyBlog\\entities\\ProjectGeneration' => $baseDir . '/src/PrintMyBlog/entities/ProjectGeneration.php',
    'PrintMyBlog\\entities\\ProjectProgress' => $baseDir . '/src/PrintMyBlog/entities/ProjectProgress.php',
    'PrintMyBlog\\entities\\SectionTemplate' => $baseDir . '/src/PrintMyBlog/entities/SectionTemplate.php',
    'PrintMyBlog\\exceptions\\TemplateDoesNotExist' => $baseDir . '/src/PrintMyBlog/exceptions/TemplateDoesNotExist.php',
    'PrintMyBlog\\factories\\FileFormatFactory' => $baseDir . '/src/PrintMyBlog/factories/FileFormatFactory.php',
    'PrintMyBlog\\factories\\ProjectFileGeneratorFactory' => $baseDir . '/src/PrintMyBlog/factories/ProjectFileGeneratorFactory.php',
    'PrintMyBlog\\factories\\ProjectGenerationFactory' => $baseDir . '/src/PrintMyBlog/factories/ProjectGenerationFactory.php',
    'PrintMyBlog\\helpers\\ArgMagician' => $baseDir . '/src/PrintMyBlog/helpers/ArgMagician.php',
    'PrintMyBlog\\orm\\entities\\Design' => $baseDir . '/src/PrintMyBlog/orm/entities/Design.php',
    'PrintMyBlog\\orm\\entities\\Project' => $baseDir . '/src/PrintMyBlog/orm/entities/Project.php',
    'PrintMyBlog\\orm\\entities\\ProjectSection' => $baseDir . '/src/PrintMyBlog/orm/entities/ProjectSection.php',
    'PrintMyBlog\\orm\\managers\\DesignManager' => $baseDir . '/src/PrintMyBlog/orm/managers/DesignManager.php',
    'PrintMyBlog\\orm\\managers\\ProjectManager' => $baseDir . '/src/PrintMyBlog/orm/managers/ProjectManager.php',
    'PrintMyBlog\\orm\\managers\\ProjectSectionManager' => $baseDir . '/src/PrintMyBlog/orm/managers/ProjectSectionManager.php',
    'PrintMyBlog\\services\\ColorGuru' => $baseDir . '/src/PrintMyBlog/services/ColorGuru.php',
    'PrintMyBlog\\services\\DebugInfo' => $baseDir . '/src/PrintMyBlog/services/DebugInfo.php',
    'PrintMyBlog\\services\\DesignRegistry' => $baseDir . '/src/PrintMyBlog/services/DesignRegistry.php',
    'PrintMyBlog\\services\\DesignTemplateRegistry' => $baseDir . '/src/PrintMyBlog/services/DesignTemplateRegistry.php',
    'PrintMyBlog\\services\\FileFormatRegistry' => $baseDir . '/src/PrintMyBlog/services/FileFormatRegistry.php',
    'PrintMyBlog\\services\\PersistentNotices' => $baseDir . '/src/PrintMyBlog/services/PersistentNotices.php',
    'PrintMyBlog\\services\\PmbCentral' => $baseDir . '/src/PrintMyBlog/services/PmbCentral.php',
    'PrintMyBlog\\services\\SectionTemplateRegistry' => $baseDir . '/src/PrintMyBlog/services/SectionTemplateRegistry.php',
    'PrintMyBlog\\services\\SvgDoer' => $baseDir . '/src/PrintMyBlog/services/SvgDoer.php',
    'PrintMyBlog\\services\\config\\Config' => $baseDir . '/src/PrintMyBlog/services/config/Config.php',
    'PrintMyBlog\\services\\generators\\PdfGenerator' => $baseDir . '/src/PrintMyBlog/services/generators/PdfGenerator.php',
    'PrintMyBlog\\services\\generators\\ProjectFileGeneratorBase' => $baseDir . '/src/PrintMyBlog/services/generators/ProjectFileGeneratorBase.php',
    'PrintMyBlog\\system\\Activation' => $baseDir . '/src/PrintMyBlog/system/Activation.php',
    'PrintMyBlog\\system\\Capabilities' => $baseDir . '/src/PrintMyBlog/system/Capabilities.php',
    'PrintMyBlog\\system\\Context' => $baseDir . '/src/PrintMyBlog/system/Context.php',
    'PrintMyBlog\\system\\CustomPostTypes' => $baseDir . '/src/PrintMyBlog/system/CustomPostTypes.php',
    'PrintMyBlog\\system\\Init' => $baseDir . '/src/PrintMyBlog/system/Init.php',
    'Twine\\admin\\news\\DashboardNews' => $baseDir . '/src/Twine/admin/news/DashboardNews.php',
    'Twine\\compatibility\\CompatibilityBase' => $baseDir . '/src/Twine/compatibility/CompatibilityBase.php',
    'Twine\\controllers\\BaseController' => $baseDir . '/src/Twine/controllers/BaseController.php',
    'Twine\\db\\TableManager' => $baseDir . '/src/Twine/db/TableManager.php',
    'Twine\\entities\\FileSubmission' => $baseDir . '/src/Twine/entities/FileSubmission.php',
    'Twine\\entities\\notifications\\OneTimeNotification' => $baseDir . '/src/Twine/entities/notifications/OneTimeNotification.php',
    'Twine\\forms\\base\\FormSection' => $baseDir . '/src/Twine/forms/base/FormSection.php',
    'Twine\\forms\\base\\FormSectionBase' => $baseDir . '/src/Twine/forms/base/FormSectionBase.php',
    'Twine\\forms\\base\\FormSectionDetails' => $baseDir . '/src/Twine/forms/base/FormSectionDetails.php',
    'Twine\\forms\\base\\FormSectionHtml' => $baseDir . '/src/Twine/forms/base/FormSectionHtml.php',
    'Twine\\forms\\base\\FormSectionHtmlFromTemplate' => $baseDir . '/src/Twine/forms/base/FormSectionHtmlFromTemplate.php',
    'Twine\\forms\\base\\FormSectionValidatable' => $baseDir . '/src/Twine/forms/base/FormSectionValidatable.php',
    'Twine\\forms\\helpers\\ImproperUsageException' => $baseDir . '/src/Twine/forms/helpers/ImproperUsageException.php',
    'Twine\\forms\\helpers\\InputOption' => $baseDir . '/src/Twine/forms/helpers/InputOption.php',
    'Twine\\forms\\helpers\\ValidationError' => $baseDir . '/src/Twine/forms/helpers/ValidationError.php',
    'Twine\\forms\\inputs\\AdminFileUploaderInput' => $baseDir . '/src/Twine/forms/inputs/AdminFileUploaderInput.php',
    'Twine\\forms\\inputs\\ButtonInput' => $baseDir . '/src/Twine/forms/inputs/ButtonInput.php',
    'Twine\\forms\\inputs\\CheckboxMultiInput' => $baseDir . '/src/Twine/forms/inputs/CheckboxMultiInput.php',
    'Twine\\forms\\inputs\\ColorInput' => $baseDir . '/src/Twine/forms/inputs/ColorInput.php',
    'Twine\\forms\\inputs\\DatepickerInput' => $baseDir . '/src/Twine/forms/inputs/DatepickerInput.php',
    'Twine\\forms\\inputs\\EmailConfirmInput' => $baseDir . '/src/Twine/forms/inputs/EmailConfirmInput.php',
    'Twine\\forms\\inputs\\EmailInput' => $baseDir . '/src/Twine/forms/inputs/EmailInput.php',
    'Twine\\forms\\inputs\\FixedHiddenInput' => $baseDir . '/src/Twine/forms/inputs/FixedHiddenInput.php',
    'Twine\\forms\\inputs\\FloatInput' => $baseDir . '/src/Twine/forms/inputs/FloatInput.php',
    'Twine\\forms\\inputs\\FontInput' => $baseDir . '/src/Twine/forms/inputs/FontInput.php',
    'Twine\\forms\\inputs\\FormInputBase' => $baseDir . '/src/Twine/forms/inputs/FormInputBase.php',
    'Twine\\forms\\inputs\\FormInputWithOptionsBase' => $baseDir . '/src/Twine/forms/inputs/FormInputWithOptionsBase.php',
    'Twine\\forms\\inputs\\HiddenInput' => $baseDir . '/src/Twine/forms/inputs/HiddenInput.php',
    'Twine\\forms\\inputs\\IntegerInput' => $baseDir . '/src/Twine/forms/inputs/IntegerInput.php',
    'Twine\\forms\\inputs\\MonthInput' => $baseDir . '/src/Twine/forms/inputs/MonthInput.php',
    'Twine\\forms\\inputs\\PasswordInput' => $baseDir . '/src/Twine/forms/inputs/PasswordInput.php',
    'Twine\\forms\\inputs\\PhoneInput' => $baseDir . '/src/Twine/forms/inputs/PhoneInput.php',
    'Twine\\forms\\inputs\\RadioButtonInput' => $baseDir . '/src/Twine/forms/inputs/RadioButtonInput.php',
    'Twine\\forms\\inputs\\SelectInput' => $baseDir . '/src/Twine/forms/inputs/SelectInput.php',
    'Twine\\forms\\inputs\\SelectMultipleInput' => $baseDir . '/src/Twine/forms/inputs/SelectMultipleInput.php',
    'Twine\\forms\\inputs\\SelectRevealInput' => $baseDir . '/src/Twine/forms/inputs/SelectRevealInput.php',
    'Twine\\forms\\inputs\\SubmitInput' => $baseDir . '/src/Twine/forms/inputs/SubmitInput.php',
    'Twine\\forms\\inputs\\TextAreaInput' => $baseDir . '/src/Twine/forms/inputs/TextAreaInput.php',
    'Twine\\forms\\inputs\\TextInput' => $baseDir . '/src/Twine/forms/inputs/TextInput.php',
    'Twine\\forms\\inputs\\YearInput' => $baseDir . '/src/Twine/forms/inputs/YearInput.php',
    'Twine\\forms\\inputs\\YesNoInput' => $baseDir . '/src/Twine/forms/inputs/YesNoInput.php',
    'Twine\\forms\\strategies\\FormInputStrategyBase' => $baseDir . '/src/Twine/forms/strategies/FormInputStrategyBase.php',
    'Twine\\forms\\strategies\\display\\AdminFileUploaderDisplay' => $baseDir . '/src/Twine/forms/strategies/display/AdminFileUploaderDisplay.php',
    'Twine\\forms\\strategies\\display\\ButtonDisplay' => $baseDir . '/src/Twine/forms/strategies/display/ButtonDisplay.php',
    'Twine\\forms\\strategies\\display\\CheckboxDisplay' => $baseDir . '/src/Twine/forms/strategies/display/CheckboxDisplay.php',
    'Twine\\forms\\strategies\\display\\CompoundInputDisplay' => $baseDir . '/src/Twine/forms/strategies/display/CompoundInputDisplay.php',
    'Twine\\forms\\strategies\\display\\DatepickerDisplay' => $baseDir . '/src/Twine/forms/strategies/display/DatepickerDisplay.php',
    'Twine\\forms\\strategies\\display\\DisplayBase' => $baseDir . '/src/Twine/forms/strategies/display/DisplayBase.php',
    'Twine\\forms\\strategies\\display\\HiddenDisplay' => $baseDir . '/src/Twine/forms/strategies/display/HiddenDisplay.php',
    'Twine\\forms\\strategies\\display\\NumberInputDisplay' => $baseDir . '/src/Twine/forms/strategies/display/NumberInputDisplay.php',
    'Twine\\forms\\strategies\\display\\RadioButtonDisplay' => $baseDir . '/src/Twine/forms/strategies/display/RadioButtonDisplay.php',
    'Twine\\forms\\strategies\\display\\Select2Display' => $baseDir . '/src/Twine/forms/strategies/display/Select2DisplayStrategy.php',
    'Twine\\forms\\strategies\\display\\SelectDisplay' => $baseDir . '/src/Twine/forms/strategies/display/SelectDisplay.php',
    'Twine\\forms\\strategies\\display\\SelectMultipleDisplay' => $baseDir . '/src/Twine/forms/strategies/display/SelectMultipleDisplay.php',
    'Twine\\forms\\strategies\\display\\SingleCheckboxDisplay' => $baseDir . '/src/Twine/forms/strategies/display/SingleCheckboxDisplay.php',
    'Twine\\forms\\strategies\\display\\SubmitInputDisplay' => $baseDir . '/src/Twine/forms/strategies/display/SubmitInputDisplay.php',
    'Twine\\forms\\strategies\\display\\TextAreaDisplay' => $baseDir . '/src/Twine/forms/strategies/display/TextAreaDisplay.php',
    'Twine\\forms\\strategies\\display\\TextInputDisplay' => $baseDir . '/src/Twine/forms/strategies/display/TextInputDisplay.php',
    'Twine\\forms\\strategies\\layout\\AdminOneColumnLayout' => $baseDir . '/src/Twine/forms/strategies/layout/AdminOneColumnLayout.php',
    'Twine\\forms\\strategies\\layout\\AdminTwoColumnLayout' => $baseDir . '/src/Twine/forms/strategies/layout/AdminTwoColumnLayout.php',
    'Twine\\forms\\strategies\\layout\\DetailsSummaryLayout' => $baseDir . '/src/Twine/forms/strategies/layout/DetailsSummaryLayout.php',
    'Twine\\forms\\strategies\\layout\\DivPerSectionLayout' => $baseDir . '/src/Twine/forms/strategies/layout/DivPerSectionLayout.php',
    'Twine\\forms\\strategies\\layout\\FieldsetSectionLayout' => $baseDir . '/src/Twine/forms/strategies/layout/FieldsetSectionLayout.php',
    'Twine\\forms\\strategies\\layout\\FormSectionLayoutBase' => $baseDir . '/src/Twine/forms/strategies/layout/FormSectionLayoutBase.php',
    'Twine\\forms\\strategies\\layout\\NoLayout' => $baseDir . '/src/Twine/forms/strategies/layout/NoLayout.php',
    'Twine\\forms\\strategies\\layout\\TemplateLayout' => $baseDir . '/src/Twine/forms/strategies/layout/TemplateLayout.php',
    'Twine\\forms\\strategies\\layout\\TwoColumnLayout' => $baseDir . '/src/Twine/forms/strategies/layout/TwoColumnLayout.php',
    'Twine\\forms\\strategies\\normalization\\AllCapsNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/AllCapsNormalization.php',
    'Twine\\forms\\strategies\\normalization\\BooleanNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/BooleanNormalization.php',
    'Twine\\forms\\strategies\\normalization\\FileNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/FileNormalization.php',
    'Twine\\forms\\strategies\\normalization\\FloatNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/FloatNormalization.php',
    'Twine\\forms\\strategies\\normalization\\IntNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/IntNormalization.php',
    'Twine\\forms\\strategies\\normalization\\ManyValuedNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/ManyValuedNormalization.php',
    'Twine\\forms\\strategies\\normalization\\NormalizationBase' => $baseDir . '/src/Twine/forms/strategies/normalization/NormalizationBase.php',
    'Twine\\forms\\strategies\\normalization\\NullNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/NullNormalization.php',
    'Twine\\forms\\strategies\\normalization\\SlugNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/SlugNormalization.php',
    'Twine\\forms\\strategies\\normalization\\TextNormalization' => $baseDir . '/src/Twine/forms/strategies/normalization/TextNormalization.php',
    'Twine\\forms\\strategies\\validation\\ConditionallyRequiredValidation' => $baseDir . '/src/Twine/forms/strategies/validation/ConditionallyRequiredValidation.php',
    'Twine\\forms\\strategies\\validation\\EmailValidation' => $baseDir . '/src/Twine/forms/strategies/validation/EmailValidation.php',
    'Twine\\forms\\strategies\\validation\\EnumValidation' => $baseDir . '/src/Twine/forms/strategies/validation/EnumValidation.php',
    'Twine\\forms\\strategies\\validation\\EqualToValidation' => $baseDir . '/src/Twine/forms/strategies/validation/EqualToValidation.php',
    'Twine\\forms\\strategies\\validation\\FloatValidation' => $baseDir . '/src/Twine/forms/strategies/validation/FloatValidation.php',
    'Twine\\forms\\strategies\\validation\\FullHtmlValidation' => $baseDir . '/src/Twine/forms/strategies/validation/FullHtmlValidation.php',
    'Twine\\forms\\strategies\\validation\\IntValidation' => $baseDir . '/src/Twine/forms/strategies/validation/IntValidation.php',
    'Twine\\forms\\strategies\\validation\\ManyValuedValidation' => $baseDir . '/src/Twine/forms/strategies/validation/ManyValuedValidation.php',
    'Twine\\forms\\strategies\\validation\\MaxLengthValidation' => $baseDir . '/src/Twine/forms/strategies/validation/MaxLengthValidation.php',
    'Twine\\forms\\strategies\\validation\\MinLengthValidation' => $baseDir . '/src/Twine/forms/strategies/validation/MinLengthValidation.php',
    'Twine\\forms\\strategies\\validation\\PlaintextValidation' => $baseDir . '/src/Twine/forms/strategies/validation/PlaintextValidation.php',
    'Twine\\forms\\strategies\\validation\\RequiredValidation' => $baseDir . '/src/Twine/forms/strategies/validation/RequiredValidation.php',
    'Twine\\forms\\strategies\\validation\\TextValidation' => $baseDir . '/src/Twine/forms/strategies/validation/TextValidation.php',
    'Twine\\forms\\strategies\\validation\\UrlValidation' => $baseDir . '/src/Twine/forms/strategies/validation/UrlValidation.php',
    'Twine\\forms\\strategies\\validation\\ValidationBase' => $baseDir . '/src/Twine/forms/strategies/validation/ValidationBase.php',
    'Twine\\helpers\\Array2' => $baseDir . '/src/Twine/helpers/Array2.php',
    'Twine\\helpers\\DateTimeHelper' => $baseDir . '/src/Twine/helpers/DateTimeHelper.php',
    'Twine\\helpers\\Html' => $baseDir . '/src/Twine/helpers/Html.php',
    'Twine\\orm\\entities\\PostWrapper' => $baseDir . '/src/Twine/orm/entities/PostWrapper.php',
    'Twine\\orm\\managers\\PostWrapperManager' => $baseDir . '/src/Twine/orm/managers/PostWrapperManager.php',
    'Twine\\services\\config\\Config' => $baseDir . '/src/Twine/services/config/Config.php',
    'Twine\\services\\display\\FormInputs' => $baseDir . '/src/Twine/services/display/FormInputs.php',
    'Twine\\services\\filesystem\\File' => $baseDir . '/src/Twine/services/filesystem/File.php',
    'Twine\\services\\filesystem\\Folder' => $baseDir . '/src/Twine/services/filesystem/Folder.php',
    'Twine\\services\\filesystem\\ThingOnServer' => $baseDir . '/src/Twine/services/filesystem/ThingOnServer.php',
    'Twine\\services\\notifications\\OneTimeNotificationManager' => $baseDir . '/src/Twine/services/notifications/OneTimeNotificationManager.php',
    'Twine\\system\\Activation' => $baseDir . '/src/Twine/system/Activation.php',
    'Twine\\system\\Context' => $baseDir . '/src/Twine/system/Context.php',
    'Twine\\system\\Init' => $baseDir . '/src/Twine/system/Init.php',
    'Twine\\system\\RequestType' => $baseDir . '/src/Twine/system/RequestType.php',
    'Twine\\system\\VersionHistory' => $baseDir . '/src/Twine/system/VersionHistory.php',
    'WPTRT\\AdminNotices\\Dismiss' => $baseDir . '/src/WPTRT/AdminNotices/Dismiss.php',
    'WPTRT\\AdminNotices\\Notice' => $baseDir . '/src/WPTRT/AdminNotices/Notice.php',
    'WPTRT\\AdminNotices\\Notices' => $baseDir . '/src/WPTRT/AdminNotices/Notices.php',
    'mnelson4\\RestApiDetector\\RestApiDetector' => $baseDir . '/src/mnelson4/RestApiDetector.php',
    'mnelson4\\RestApiDetector\\RestApiDetectorError' => $baseDir . '/src/mnelson4/RestApiDetectorError.php',
);
