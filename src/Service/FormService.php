<?php

namespace App\Service;

use App\Repository\FormRepository;
use App\Repository\FormFieldsRepository;
use App\Repository\FieldOptionsRepository;

class FormService extends Service
{
    protected FormFieldsRepository $fields;
    protected FieldOptionsRepository $fieldOptions;

    public function __construct(
        FormRepository $repository,
        FormFieldsRepository $fields,
        FieldOptionsRepository $fieldOptions
    ) {
        parent::__construct($repository);
        $this->fields = $fields;
        $this->fieldOptions = $fieldOptions;
    }

    public function getAllActiveForms()
    {
        return $this->getBy([
            "is_active" => 1,
        ]);
    }
}
