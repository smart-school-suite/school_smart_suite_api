<?php

namespace Tests\Feature\RichText;

use App\Http\Requests\Concerns\SanitizesRichText;
use App\Rules\RichTextMax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SanitizesRichTextTest extends TestCase
{
    private function resolve(array $payload): FormRequest
    {
        $request = SanitizesRichTextTestRequest::createFrom(Request::create('/test', 'POST', $payload));
        $request->setContainer($this->app)->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        return $request;
    }

    public function test_top_level_and_nested_fields_are_sanitized_before_validation(): void
    {
        $request = $this->resolve([
            'description' => '<p onclick="x()">Hello <script>alert(1)</script><strong>there</strong></p>',
            'items' => [
                ['description' => '<img src="x" onerror="alert(1)"><p>One</p>'],
                ['description' => '<p>Two</p>'],
            ],
            'title' => '<b>not a rich-text field</b>',
        ]);

        $this->assertSame('<p>Hello <strong>there</strong></p>', $request->validated()['description']);
        $this->assertSame('<p>Hello <strong>there</strong></p>', $request->input('description'));
        $this->assertSame('<p>One</p>', $request->validated()['items'][0]['description']);
        $this->assertSame('<p>Two</p>', $request->validated()['items'][1]['description']);
        $this->assertSame('<b>not a rich-text field</b>', $request->input('title'));
    }

    public function test_empty_editor_value_fails_required(): void
    {
        $this->expectException(ValidationException::class);

        $this->resolve(['description' => '<p></p>']);
    }

    public function test_length_limit_counts_visible_text_not_markup(): void
    {
        $request = $this->resolve(['description' => '<p><strong><em><u>' . str_repeat('a', 20) . '</u></em></strong></p>']);
        $this->assertNotNull($request->validated()['description']);

        $this->expectException(ValidationException::class);
        $this->resolve(['description' => '<p>' . str_repeat('a', 21) . '</p>']);
    }
}

class SanitizesRichTextTestRequest extends FormRequest
{
    use SanitizesRichText;

    protected function richTextFields(): array
    {
        return ['description', 'items.*.description'];
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', new RichTextMax(20)],
            'items' => 'sometimes|array',
            'items.*.description' => 'sometimes|nullable|string',
            'title' => 'sometimes|string',
        ];
    }
}
