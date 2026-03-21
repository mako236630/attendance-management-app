<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;
use App\Models\Rest;

class DetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "in_time" => 'required',
            "out_time" => 'required',
            "note" => 'required|string|max:20',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $inTime = $this->in_time;
            $outTime = $this->out_time;

            if ($inTime && $outTime && $inTime >= $outTime) {
                $validator->errors()->add('time_error', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($this->rests) {
                foreach ($this->rests as $id => $rest) {
                    $restIn = $rest['in'];
                    $restOut = $rest['out'];

                    if (!empty($restIn)) {
                        if ($restIn && ($restIn < $inTime || $restIn > $outTime)) {
                            $validator->errors()->add("rests.$id.in", '休憩時間が不適切な値です');
                        }
                    }

                    if (!empty($restOut)) {
                        if ($restOut && $restOut > $outTime) {
                            $validator->errors()->add("rests.$id.out", '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }

                    if (!empty($restIn) && !empty($restOut)) {
                        if ($restIn && $restOut && $restIn >= $restOut) {
                            $validator->errors()->add("rests.$id.in", '休憩時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            "in_time.required" => "出勤時間を入力してください",
            "out_time.required" => "退勤時間を入力してください",
            "note.required" => "備考を記入してください",
            "note.max" => "備考は20文字以内で入力してください",
        ];
    }
}
