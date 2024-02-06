<?php

namespace Code16\JockoClient\JockoCms\Form;

abstract class JockoField
{
    protected ?string $label = null;
    protected ?string $helpMessage = null;
    protected array $validationRules = [];
    protected bool $isUpdatable = true;

    public function __construct(protected string $key)
    {
    }

    public static function makeText(string $key): JockoTextField
    {
        return new JockoTextField($key);
    }

    public static function makeDate(string $key): JockoDateField
    {
        return new JockoDateField($key);
    }

    public static function makeFileList(string $key): JockoFileListField
    {
        return new JockoFileListField($key);
    }

    public static function makeImageList(string $key): JockoImageListField
    {
        return new JockoImageListField($key);
    }

    public static function makeImage(string $key): JockoImageField
    {
        return new JockoImageField($key);
    }

    public static function makeSelect(string $key): JockoSelectField
    {
        return new JockoSelectField($key);
    }

    public static function makeCheck(string $key, string $text): JockoCheckField
    {
        return new JockoCheckField($key, $text);
    }

    public static function makeEditor(string $key): JockoEditorField
    {
        return new JockoEditorField($key);
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
