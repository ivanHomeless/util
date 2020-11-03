<?php

class UploadedFile
{
    public $filename;

    public $uniqueName;

    public $extension;

    public $type;

    public $size;

    private $tmpName;


    /**
     * UploadedFile constructor.
     * @param $filename
     * @param $type
     * @param $size
     * @param $tmpName
     */
    public function __construct($filename, $type, $size, $tmpName)
    {
        $info = pathinfo($filename);
        $this->filename = $filename;
        $this->extension = $info['extension'];
        $this->type = $type;
        $this->size = $size;
        $this->tmpName = $tmpName;
        $this->uniqueName = uniqid() . '.' . $this->extension;
    }

    /**
     * @param $fieldName
     * @return UploadedFile
     */
    public static function getInstance($fieldName): UploadedFile
    {
        $file = $_FILES[$fieldName];
        return new static($file['name'], $file['type'], $file['size'], $file['tmp_name']);
    }

    /**
     * @param string $fieldName
     * @return array
     */
    public static function getInstances(string $fieldName): array
    {
        $files = $_FILES[$fieldName];
        $fileList = [];
        if (is_array($files['name'])) {
            foreach ($files['name'] as $k => $tmpName) {
                $fileList[] = new static($files['name'][$k], $files['type'][$k], $files['size'][$k], $files['tmp_name'][$k]);
            }
        }
        return $fileList;
    }

    /**
     * @param $dir
     * @param null $name
     * @return string
     */
    public function saveAs($dir, $name = null): string
    {
        $path = realpath($dir);
        $name = ($name) ? $name : $this->uniqueName;
        if (file_exists($path)) {
            move_uploaded_file($this->tmpName, $path . '/' . $name);
        }
        return $name;
    }
}
