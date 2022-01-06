<?php

namespace App\Extensions\HTML;

class Form extends \Illuminate\Html\FormBuilder
{
    public function tel($name, $value = null, $options = [])
    {
        return $this->input('tel', $name, $value, $options);
    }

    public function number($name, $value = null, $options = [])
    {
        return $this->input('number', $name, $value, $options);
    }

    public function checkboxInline($name, $value = 1, $checked = null, $options = [], $labelText = null, $labelOptions = [])
    {
        $this->labels[] = $name;

        $labelOptions = $this->html->attributes($labelOptions);

        $labelText = e($this->formatLabel($name, $labelText));

        return '<label ' . $labelOptions . '>' . $this->checkable('checkbox', $name, $value, $checked, $options) . $labelText . '</label>';
    }

    public function radioInline($name, $value = null, $checked = null, $options = [], $labelText = null, $labelOptions = [])
    {
        if (is_null($value)) $value = $name;

        $this->labels[] = $name;

        $labelOptions = $this->html->attributes($labelOptions);

        $labelText = e($this->formatLabel($name, $labelText));

        return '<label ' . $labelOptions . '>' . $this->checkable('radio', $name, $value, $checked, $options) . $labelText . '</label>';
    }

    public function multiselect($name, $data = [], $options = [])
    {
        $options['id'] = $this->getIdAttribute($name, $options);

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $html = [];

        if (count($data)) {
            foreach ($data['options'] as $option) {
                if (isset($option['optgroup'])) {
                    $html[] = $this->multiselectOptionGroup($data, $option);
                } else {
                    $html[] = $this->multiselectOption($data, $option);
                }
            }
        }

        $options = $this->html->attributes($options);

        $list = implode('', $html);

        return "<select {$options}>{$list}</select>";
    }

    protected function multiselectOptionGroup($data, $options)
    {
        $html = [];

        foreach ($options['optgroup'] as $option) {
            $html[] = $this->multiselectOption($data, $option);
        }

        return '<optgroup label="' . e($options[$data['name']]) . '">' . implode('', $html) . '</optgroup>';
    }

    protected function multiselectOption($data, $option)
    {
        $selected = $this->getSelectedValue($option[$data['id']], $data['selected']);

        $options = ['value' => e($option[$data['id']]), 'selected' => $selected, 'data-sub-text' => ((isset($data['subText']) && isset($option[$data['subText']])) ? e($option[$data['subText']]) : '')];

        if (isset($data['data'])) {
            foreach ($data['data'] as $key => $val) {
                if (isset($option[$val])) {
                    $options += ['data-' . $key => e($option[$val])];
                }
            }
        }

        return '<option' . $this->html->attributes($options) . '>' . e($option[$data['name']]) . '</option>';
    }
}
