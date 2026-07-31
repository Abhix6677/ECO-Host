<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route already protected by `auth` middleware
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\_\.]+$/'],
            'source_type' => ['nullable', 'string', 'in:zip,github'],
            'zip_file'    => [
                'required_without:github_url',
                'nullable',
                'file',
                'mimes:zip',        // Must be a zip file
                'max:102400',       // Max 100 MB (in kilobytes)
            ],
            'github_url'  => [
                'required_without:zip_file',
                'nullable',
                'url',
                'regex:/^https:\/\/github\.com\/[^\/]+\/[^\/]+/i',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Please give your website project a name.',
            'name.regex'                => 'Website name may only contain letters, numbers, spaces, hyphens, underscores, and dots.',
            'name.max'                  => 'Website name cannot exceed 100 characters.',
            'zip_file.required_without' => 'Please select a website.zip file or enter a GitHub repository URL.',
            'zip_file.mimes'            => 'Only .zip archives are accepted. Please package your site files into a ZIP first.',
            'zip_file.max'              => 'ZIP file size must not exceed 100 MB.',
            'github_url.required_without' => 'Please enter a valid public GitHub repository URL.',
            'github_url.regex'          => 'GitHub URL format should be: https://github.com/username/repository',
        ];
    }
}
