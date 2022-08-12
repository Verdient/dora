<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation;

use Hyperf\Contract\TranslatorInterface;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Arr;
use Hyperf\Utils\MessageBag;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Formatter;
use Verdient\Dora\Utils\Container;

/**
 * @inheritdoc
 * @author Verdient。
 */
class SpreadsheetValidator extends Validator
{
    /**
     * @var array 头部信息
     * @author Verdient。
     */
    protected $headers = [];

    /**
     * @var int 当前的行
     * @author Verdient。
     */
    protected $currentRow = 0;

    /**
     * @var bool 文件是否已解析
     * @author Verdient。
     */
    protected $isFileParsed = false;

    /**
     * @var string 文件名
     * @author Verdient。
     */
    protected $fileName = null;

    /**
     * @var int 最少需要的行数
     * @author Verdient。
     */
    protected $minRows = null;

    /**
     * @var int 最多允许的行数
     * @author Verdient。
     */
    protected $maxRows = null;

    /**
     * @var int 最大允许的文件大小
     * @author Verdient。
     */
    protected $maxFilesize = null;

    /**
     * @var int 数据起始行
     * @author Verdient。
     */
    protected $dataRowStartIndex = 2;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(
        TranslatorInterface $translator,
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = [],
        $fileName,
        int $minRows = null,
        int $maxRows = null,
        int $maxFilesize = null,
        int $dataRowStartIndex = 2
    ) {
        $this->addReplacer('min_rows', function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
            return str_replace(':min', $parameters['min'], $message);
        });
        $this->addReplacer('max_rows', function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
            return str_replace(':max', $parameters['max'], $message);
        });
        $this->addReplacer('distinct_header', function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
            return str_replace(':headers', $parameters['headers'], $message);
        });
        $this->addReplacer('missing_header', function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
            return str_replace(':headers', $parameters['headers'], $message);
        });
        $this->addReplacer('no_formula', function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) {
            return str_replace(':coordinate', $parameters['coordinate'], $message);
        });
        if ($dataRowStartIndex < 2) {
            $dataRowStartIndex = 2;
        }
        $this->initialRules = $rules;
        $this->translator = $translator;
        $this->customMessages = $messages;
        $this->customAttributes = $customAttributes;
        $this->fileName = $fileName;
        $this->messages = new MessageBag();
        $this->minRows = $minRows;
        $this->maxRows = $maxRows;
        $this->maxFilesize = $maxFilesize;
        $this->dataRowStartIndex = $dataRowStartIndex;
        $this->data = $this->parseData($data);
        $this->setRules($rules);
    }

    /**
     * 设置头部信息
     * @param array $headers 头部信息
     * @return static
     * @author Verdient。
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * 文件的校验规则
     * @return array
     * @author Verdient。
     */
    protected function fileRules()
    {
        $rules = [
            'required',
            'file',
            ['mimes', 'xlsx', 'xls', 'csv'],
            ['mimetypes', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv', 'text/plain']
        ];
        if ($this->maxFilesize > 0) {
            $rules[] =  ['max', $this->maxFilesize];
        }
        return $rules;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parseData(array $data): array
    {
        if (!$this->isFileParsed) {
            $this->isFileParsed = true;
            /**
             * @var ValidatorFactoryInterface
             */
            $factory = Container::get(ValidatorFactoryInterface::class);
            $validator = $factory->make(
                $data,
                [$this->fileName => $this->fileRules()],
            );
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            return $this->parseSpreadsheetData($data[$this->fileName]);
        }
        return $data;
    }

    /**
     * 解析电子表格数据
     * @return array
     * @author Verdient。
     */
    protected function parseSpreadsheetData(UploadedFile $file): array
    {
        $extension = $file->getExtension();
        $reader = IOFactory::createReader(ucfirst($extension));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $dataRowsCount = $highestRow - ($this->dataRowStartIndex - 1);
        if ($this->minRows > 0 && $dataRowsCount < $this->minRows) {
            $this->addFailure($file->getClientFilename(), 'min_rows', ['min' => $this->minRows]);
            return [];
        }
        if ($this->maxRows > 0 && $dataRowsCount > $this->maxRows) {
            $this->addFailure($file->getClientFilename(), 'max_rows', ['max' => $this->maxRows]);
            return [];
        }
        if ($highestRow < $this->dataRowStartIndex) {
            return [];
        }
        $this->headers = [];
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($headerName = $cell->getFormattedValue()) {
                    $this->headers[$cell->getColumn()] = $headerName;
                }
            }
        }
        $repeatedHeaders = array_diff_assoc($this->headers, array_unique($this->headers));
        if (!empty($repeatedHeaders)) {
            $headers = [];
            foreach ($repeatedHeaders as $column => $name) {
                $headers[] = $name . ' @ ' . $column . '1';
            }
            $this->addFailure($file->getClientFilename(), 'distinct_header', ['headers' => implode(', ', $headers)]);
            return [];
        }
        $attributes = array_flip($this->customAttributes);
        $missingHeaders = array_diff(array_keys($attributes), $this->headers);
        if (!empty($missingHeaders)) {
            $this->addFailure($file->getClientFilename(), 'missing_header', ['headers' => implode(', ', $missingHeaders)]);
            return [];
        }
        $data = [];
        foreach ($worksheet->getRowIterator($this->dataRowStartIndex) as $row) {
            $rowData = [];
            foreach ($row->getCellIterator('A', array_key_last($this->headers)) as $cell) {
                $column = $cell->getColumn();
                if (isset($attributes[$this->headers[$column]])) {
                    $attribute = $attributes[$this->headers[$column]];
                    if (substr($attribute, 0, 2) === '*.') {
                        $attribute = substr($attribute, 2);
                    }
                    if ($cell->isFormula()) {
                        $this->addFailure($file->getClientFilename(), 'no_formula', ['coordinate' => $cell->getCoordinate()]);
                        return [];
                    }
                    $value = $cell->getValue();
                    if (is_object($value)) {
                        $value = (string) $value;
                    }
                    if (is_string($value)) {
                        $value = trim($value);;
                    }
                    if (is_int($value)) {
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        if ($format === 'yyyy"年"m"月"d"日";@') {
                            $format = 'yyyy/m/d;@';
                        }
                        $value = Formatter::toFormattedString($value, $format);
                        if (is_string($value)) {
                            $value = trim($value);
                        }
                    }
                    $rowData[$attribute] = $value;
                }
            }
            $data[] = $rowData;
        }
        $this->headers = array_flip($this->headers);
        return $data;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function passes(): bool
    {
        $rawData = $this->data;
        $this->distinctValues = [];
        $this->failedRules = [];
        $this->currentRow = $this->dataRowStartIndex;
        foreach ($rawData as &$row) {
            foreach ($row as $attribute => $value) {
                if ($this->hasRule($attribute, 'AsDate')) {
                    if (is_int($value)) {
                        Arr::set($row, $attribute, Formatter::toFormattedString($value, 'yyyy/m/d;@'));
                    }
                }
            }
            $this->data = $row;
            foreach ($this->rules as $attribute => $rules) {
                $attribute = str_replace('\.', '->', $attribute);
                foreach ($rules as $rule) {
                    $this->validateAttribute($attribute, $rule);
                    if ($this->messages->has($attribute)) {
                        return false;
                    }
                }
            }
            foreach ($this->after as $after) {
                call_user_func($after);
            }
            $this->currentRow++;
        }
        $this->data = $rawData;
        return $this->messages->isEmpty();
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function isValidatable($rule, string $attribute, $value): bool
    {
        if ($rule === 'AsDate') {
            return false;
        }
        return parent::isValidatable($rule, $attribute, $value);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getDisplayableAttribute(string $attribute): string
    {
        $message = parent::getDisplayableAttribute($attribute);
        if (isset($this->headers[$message])) {
            $message .= ' @ ' . $this->headers[$message] . $this->currentRow;
        }
        return $message;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validateDistinct(string $attribute, $value, array $parameters): bool
    {
        if (!isset($this->distinctValues[$attribute])) {
            $this->distinctValues[$attribute] = [];
        }
        if (in_array($value, $this->distinctValues[$attribute])) {
            return false;
        }
        $this->distinctValues[$attribute][] = $value;
        return true;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validated(): array
    {
        if ($this->invalid()) {
            throw new ValidationException($this);
        }
        return $this->data;
    }
}
