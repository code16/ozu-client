<?php

namespace Code16\OzuClient\OzuCms\Form;

abstract class OzuField
{
    protected ?string $label = null;
    protected ?string $helpMessage = null;
    protected array $validationRules = [];
    protected bool $isUpdatable = true;

    public function __construct(protected string $key)
    {
    }

    public static function makeText(string $key): OzuTextField
    {
        return new OzuTextField($key);
    }

    public static function makeDate(string $key): OzuDateField
    {
        return new OzuDateField($key);
    }

    public static function makeFileList(string $key): OzuFileListField
    {
        return new OzuFileListField($key);
    }

    public static function makeImageList(string $key): OzuImageListField
    {
        return new OzuImageListField($key);
    }

    public static function makeImage(string $key): OzuImageField
    {
        return new OzuImageField($key);
    }

    public static function makeSelect(string $key): OzuSelectField
    {
        return new OzuSelectField($key);
    }

    public static function makeCheck(string $key, string $text): OzuCheckField
    {
        return new OzuCheckField($key, $text);
    }

    public static function makeEditor(string $key): OzuEditorField
    {
        return new OzuEditorField($key);
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setHelpMessage(string $helpMessage)
    {
        $this->helpMessage = $helpMessage;

        return $this;
    }

    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;

        return $this;
    }

    public function setIsUpdatable(bool $isUpdatable = true): self
    {
        $this->isUpdatable = $isUpdatable;

        return $this;
    }

    abstract public function type(): string;

    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'key' => $this->key,
            'label' => $this->label,
            'validationRules' => $this->validationRules,
            'helpMessage' => $this->helpMessage,
            'isUpdatable' => $this->isUpdatable,
        ];
    }
}
