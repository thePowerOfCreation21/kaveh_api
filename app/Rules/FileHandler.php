<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FileHandler implements Rule
{
    protected $handler;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(callable $handler = null)
    {
        $this->handler = $handler;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        /*
        $request = new Request();
        $request->replace([$attribute => $this->handle_file($value)]);
        // dd($request->input('image'));
        */
        return true;
    }

    /**
     * @param UploadedFile|null $file
     * @return false|string|null
     */
    protected function handle_file (UploadedFile $file = null): bool|string|null
    {
        if (empty($file))
        {
            return null;
        }
        if (is_callable($this->handler))
        {
            return ($this->handler)($file);
        }
        return $file->store('/uploads');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
