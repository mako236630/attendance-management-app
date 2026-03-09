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
            "note" => 'required|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $inTime = $this->in_time;
            $outTime = $this->out_time;

            // 1. 出勤時間が退勤時間より後、または退勤時間が出勤時間より前
            if ($inTime && $outTime && $inTime >= $outTime) {
                $validator->errors()->add('time_error', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($this->rests) {
                foreach ($this->rests as $id => $rest) {
                    $restIn = $rest['in'];
                    $restOut = $rest['out'];

                    // 2. 休憩開始時間が出勤前、または退勤後
                    if ($restIn && ($restIn < $inTime || $restIn > $outTime)) {
                        $validator->errors()->add("rests.$id.in", '休憩時間が不適切な値です');
                    }

                    // 3. 休憩終了時間が退勤時間より後
                    if ($restOut && $restOut > $outTime) {
                        $validator->errors()->add("rests.$id.out", '休憩時間もしくは退勤時間が不適切な値です');
                    }

                    // (追加) 休憩開始が休憩終了より後の場合も不適切として扱う
                    if ($restIn && $restOut && $restIn >= $restOut) {
                        $validator->errors()->add("rests.$id.in", '休憩時間が不適切な値です');
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            "note.required" => "備考を記入してください",
            "note.max" => "備考は255文字以内で入力してください",
        ];
    }
}
