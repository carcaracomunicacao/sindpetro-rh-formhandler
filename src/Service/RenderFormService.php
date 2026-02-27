<?php

namespace App\Service;

class RenderFormService extends FormService
{
    public function getFullFormData(string $uuid): ?array
    {
        // Usa o método 'getBy' da classe Service pai (que usa o FormRepository)
        $form = $this->getBy(['uuid' => $uuid], [], true);
        
        if (!$form) return null;

        // Busca os campos através do repositório injetado
        $fields = $this->fields->findBy(['form_id' => $form['id']], ['display_order' => 'ASC']);

        // Hidrata os campos que possuem opções (select, radio, checkbox)
        foreach ($fields as &$field) {
            if (in_array($field['field_type'], ['select', 'radio', 'checkbox'])) {
                $field['options'] = $this->fieldOptions->findBy(['field_id' => $field['id']]);
            }
        }

        return [
            'form' => $form,
            'fields' => $fields
        ];
    }
}