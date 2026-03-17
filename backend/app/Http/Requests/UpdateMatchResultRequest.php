<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $maxGoals = max(0, (int) config('league.max_goals_per_team', 20));

        return [
            'homeGoals' => ['required', 'integer', 'min:0', 'max:' . $maxGoals],
            'awayGoals' => ['required', 'integer', 'min:0', 'max:' . $maxGoals],
        ];
    }
}
