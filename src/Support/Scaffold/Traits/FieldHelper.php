<?php

namespace Tir\Crud\Support\Scaffold\Traits;

/**
 * FieldHelper Trait - Clean API for field creation and management
 *
 * This trait provides a user-friendly interface for creating fields
 * and accessing field-related functionality without dealing with
 * complex internal APIs.
 *
 * Usage:
 * use Tir\Crud\Support\Scaffold\FieldHelper;
 *
 * class MyScaffolder extends BaseScaffolder
 * {
 *     use FieldHelper;
 *
 *     public function setFields(): array
 *     {
 *         return [
 *             $this->text('name'),
 *             $this->password('password'),
 *         ];
 *     }
 *
 *     public function customMethod()
 *     {
 *         $allFields = $this->getAllDataFields();  // Clean API
 *         $indexFields = $this->getIndexFields();   // Simple access
 *     }
 * }
 */
trait FieldHelper
{
    /**
     * Create a Text field
     */
    protected function text(string $name): \Tir\Crud\Support\Scaffold\Fields\Text
    {
        return \Tir\Crud\Support\Scaffold\Fields\Text::make($name);
    }

    /**
     * Create a TextArea field
     */
    protected function textArea(string $name): \Tir\Crud\Support\Scaffold\Fields\TextArea
    {
        return \Tir\Crud\Support\Scaffold\Fields\TextArea::make($name);
    }

    /**
     * Create a Password field
     */
    protected function password(string $name): \Tir\Crud\Support\Scaffold\Fields\Password
    {
        return \Tir\Crud\Support\Scaffold\Fields\Password::make($name);
    }

    /**
     * Create a Number field
     */
    protected function number(string $name): \Tir\Crud\Support\Scaffold\Fields\Number
    {
        return \Tir\Crud\Support\Scaffold\Fields\Number::make($name);
    }

    /**
     * Create a Select field
     */
    protected function select(string $name): \Tir\Crud\Support\Scaffold\Fields\Select
    {
        return \Tir\Crud\Support\Scaffold\Fields\Select::make($name);
    }

    /**
     * Create a CheckBox field
     */
    protected function checkBox(string $name): \Tir\Crud\Support\Scaffold\Fields\CheckBox
    {
        return \Tir\Crud\Support\Scaffold\Fields\CheckBox::make($name);
    }

    /**
     * Create a SwitchBox field
     */
    protected function switchBox(string $name): \Tir\Crud\Support\Scaffold\Fields\SwitchBox
    {
        return \Tir\Crud\Support\Scaffold\Fields\SwitchBox::make($name);
    }

    /**
     * Create a DatePicker field
     */
    protected function datePicker(string $name): \Tir\Crud\Support\Scaffold\Fields\DatePicker
    {
        return \Tir\Crud\Support\Scaffold\Fields\DatePicker::make($name);
    }

    /**
     * Create a FileUploader field
     */
    protected function fileUploader(string $name): \Tir\Crud\Support\Scaffold\Fields\FileUploader
    {
        return \Tir\Crud\Support\Scaffold\Fields\FileUploader::make($name);
    }

    /**
     * Create an Additional (JSON) field
     */
    protected function additional(string $name): \Tir\Crud\Support\Scaffold\Fields\Additional
    {
        return \Tir\Crud\Support\Scaffold\Fields\Additional::make($name);
    }

    /**
     * Create a ColorPicker field
     */
    protected function colorPicker(string $name): \Tir\Crud\Support\Scaffold\Fields\ColorPicker
    {
        return \Tir\Crud\Support\Scaffold\Fields\ColorPicker::make($name);
    }

    /**
     * Create an Editor field
     */
    protected function editor(string $name): \Tir\Crud\Support\Scaffold\Fields\Editor
    {
        return \Tir\Crud\Support\Scaffold\Fields\Editor::make($name);
    }

    /**
     * Create a Radio field
     */
    protected function radio(string $name): \Tir\Crud\Support\Scaffold\Fields\Radio
    {
        return \Tir\Crud\Support\Scaffold\Fields\Radio::make($name);
    }

    /**
     * Create a Slug field
     */
    protected function slug(string $name): \Tir\Crud\Support\Scaffold\Fields\Slug
    {
        return \Tir\Crud\Support\Scaffold\Fields\Slug::make($name);
    }

    /**
     * Create a Button field
     */
    protected function button(string $name): \Tir\Crud\Support\Scaffold\Fields\Button
    {
        return \Tir\Crud\Support\Scaffold\Fields\Button::make($name);
    }

    /**
     * Create a Link field
     */
    protected function link(string $name): \Tir\Crud\Support\Scaffold\Fields\Link
    {
        return \Tir\Crud\Support\Scaffold\Fields\Link::make($name);
    }

    /**
     * Create a Custom field
     */
    protected function custom(string $name): \Tir\Crud\Support\Scaffold\Fields\Custom
    {
        return \Tir\Crud\Support\Scaffold\Fields\Custom::make($name);
    }

    // =================================================================
    // FIELD MANAGEMENT HELPERS - Clean API for common operations
    // =================================================================

    /**
     * Get all data fields from this scaffolder
     * Clean alternative to: $this->fieldsHandler()->getAllDataFields()
     */
    // protected function getAllDataFields(): array
    // {
    //     return $this->fieldsHandler()->getAllDataFields();
    // }

    // /**
    //  * Get fields displayed on index page
    //  * Clean alternative to: $this->fieldsHandler()->getIndexFields()
    //  */
    // protected function getIndexFields(): array
    // {
    //     return $this->fieldsHandler()->getIndexFields();
    // }

    // /**
    //  * Get fields displayed on edit/create forms
    //  * Clean alternative to: $this->fieldsHandler()->getEditFields()
    //  */
    // protected function getEditFields(): array
    // {
    //     return $this->fieldsHandler()->getEditFields();
    // }

    // /**
    //  * Get fillable fields for model operations
    //  * Clean alternative to: $this->fieldsHandler()->getFillableFields()
    //  */
    // protected function getFillableFields(): array
    // {
    //     return $this->fieldsHandler()->getFillableFields();
    // }
}
