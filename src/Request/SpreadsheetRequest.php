<?php

declare(strict_types=1);

namespace Verdient\Dora\Request;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException as ValidationValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Formatter;
use Verdient\Dora\Exception\ValidationException;
use Verdient\Dora\Validation\SpreadsheetValidator;
use Verdient\Dora\Validation\ValidatorFactory;

/**
 * 电子表格请求
 * @author Verdient。
 */
class SpreadsheetRequest extends AbstractRequest implements ValidatesWhenResolved
{
    /**
     * @var array 头部信息
     * @author Verdient。
     */
    protected $headers = [];

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 文件的校验规则
     * @return array
     * @author Verdient。
     */
    protected function fileRules()
    {
        return [
            'required',
            'file',
            ['mimes', 'xlsx', 'xls', 'csv'],
            ['mimetypes', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv', 'text/plain']
        ];
    }

    /**
     * 获取文件名称
     * @return string
     * @author Verdient。
     */
    protected function fileName()
    {
        return 'file';
    }

    /**
     * 校验规则
     * @return array
     * @author Verdient。
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function validationData(): array
    {
        $files = $this->getUploadedFiles();
        $factory = $this->container->get(ValidatorFactoryInterface::class);
        $validator = $factory->make(
            $files,
            [$this->fileName() => $this->fileRules()],
        );
        if (!$validator->passes()) {
            throw new ValidationValidationException($validator);
        }
        $file = $validator->validated()[$this->fileName()];
        $file = $files[$this->fileName()];
        $extension = $file->getExtension();
        $reader = IOFactory::createReader(ucfirst($extension));
        $spreadsheet = $reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        if ($worksheet->getHighestRow() < 2) {
            throw new ValidationException('文件中至少需包含两行数据');
        } else {
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
                $message = '存在重复的表头: ';
                foreach ($repeatedHeaders as $column => $name) {
                    $message .= $name . ' @ ' . $column . '1, ';
                }
                throw new ValidationException(substr($message, 0, -2));
            }
            $attributes = array_flip($this->attributes());
            $missingHeaders = array_diff(array_keys($attributes), $this->headers);
            /*if (!empty($missingHeaders)) {
                throw new ValidationException('缺少表头 ' . implode(', ', $missingHeaders));
            }*/
            $data = [];
            foreach ($worksheet->getRowIterator(2) as $row) {
                $rowData = [];
                foreach ($row->getCellIterator('A', array_key_last($this->headers)) as $cell) {
                    $column = $cell->getColumn();
                    if (isset($attributes[$this->headers[$column]])) {
                        $attribute = $attributes[$this->headers[$column]];
                        if (substr($attribute, 0, 2) === '*.') {
                            $attribute = substr($attribute, 2);
                        }
                        if ($cell->isFormula()) {
                            throw new ValidationException('请勿包含公式 @' . $cell->getCoordinate());
                        }
                        $value = $cell->getValue();
                        if (is_string($value)) {
                            $value = trim($value);;
                        }
                        if (is_int($value)) {
                            $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                            if ($format === 'yyyy"年"m"月"d"日";@') {
                                $format = 'm/d/yyyy';
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
            return $data;
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validator(ValidatorFactory $factory): ValidatorInterface
    {
        return $factory->makeCustom($this->validationData(), $this->rules(), $this->messages(), $this->attributes(), function (...$args) {
            $validator = new SpreadsheetValidator(...$args);
            $validator->setHeaders(array_flip($this->headers));
            return $validator;
        });
    }
}
