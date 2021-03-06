<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Applicant;
use App\RequestLetter;

class OutgoingLetter extends Model
{
    use SoftDeletes;
    protected $table = 'outgoing_letters';

    const APPROVED = 'approved';
    const NOT_APPROVED = 'not_approved';
    
    protected $fillable = [
        'user_id',
        'letter_number',
        'letter_date',
        'status',
        'filename'
    ];

    /**
     * Get total request letter by Outgoing Letter ID
     */
    public function requestLetter()
    {
        return $this->hasMany('App\RequestLetter', 'outgoing_letter_id', 'id');
    }

    /**
     * Function to return filename if exists 
     *
     * @param [int] $value
     * @return string / null
     */
    public function getFileAttribute($value)
    {
        $data = FileUpload::find($value);
        if (!$data) {
            return null;
        } elseif (substr($data->name, 0, 12) === 'outgoing_letter') {
            return env('AWS_CLOUDFRONT_URL') . $data->name;
        } else {
            return $data->name;
        }
    }

    /**
     * Function to return Request Letter Total
     *
     * @param [int] $this->id
     * @return integer
     */
    public function getRequestLetterTotalAttribute()
    {
        return RequestLetter::where('outgoing_letter_id', $this->id)
        ->join('applicants', 'applicants.id', '=', 'request_letters.applicant_id')
        ->where('applicants.verification_status', '=', Applicant::STATUS_VERIFIED)
        ->count();
    }
}
