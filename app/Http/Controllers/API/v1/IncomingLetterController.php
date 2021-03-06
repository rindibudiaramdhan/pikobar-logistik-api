<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Applicant;
use App\RequestLetter;
use App\Agency;
use DB;

class IncomingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = []; 
        $limit = $request->input('limit', 10);
        $sort = $request->filled('sort') ? ['applicants.application_letter_number ' . $request->input('sort') ] : ['applicants.created_at ASC'];

        try {
            $data = Applicant::select(
                    'applicants.id',
                    'applicants.application_letter_number as letter_number',
                    'applicants.agency_id',  
                    'agency.agency_name',  
                    'agency.location_district_code as district_code',  
                    'districtcities.kemendagri_kabupaten_nama as district_name',  
                    'applicants.applicant_name',
                    'applicants.created_at as letter_date',
                    DB::raw('"Belum Ada Surat Keluar" as status')
                )
                ->where(function ($query) use ($request) {
                    if ($request->filled('letter_date')) {
                        $query->whereDate('applicants.created_at', '=', $request->input('letter_date'));
                    }
                    if ($request->filled('district_code')) {
                        $query->where('agency.location_district_code', '=', $request->input('district_code'));
                    }
                    if ($request->filled('agency_id')) {
                        $query->where('applicants.agency_id', '=', $request->input('agency_id'));
                    }
                    if ($request->filled('letter_number')) {
                        $query->where('applicants.application_letter_number', 'LIKE', "%{$request->input('letter_number')}%");
                    }
                })
                ->join('agency', 'agency.id', '=', 'applicants.agency_id')
                ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
                ->where('applicants.is_deleted', '!=', 1)
                ->orderByRaw(implode($sort))->paginate($limit);

            $data->getCollection()->transform(function ($applicant, $key) {
                $find = RequestLetter::where('applicant_id', $applicant->id)->first();
                $applicant->status = $find ? 'Ada Surat Keluar' : $applicant->status;
                $applicant->letter_date = date('Y-m-d', strtotime($applicant->letter_date));
                return $applicant;
            });
            
            if ($request->filled('status')) {
                foreach ($data as $key => $applicant) {
                    if ($applicant->status != $request->input('status')) {
                        unset($data[$key]);
                    }
                }
            }
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }
    
    /**
     * Display a listing of the resource.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $data = []; 
        
        try {
            $data = Applicant::select(
                'applicants.id',
                'applicants.application_letter_number as letter_number',
                'applicants.agency_id',   
                'applicants.applicants_office', 
                'applicants.file', 
                'applicants.email', 
                'applicants.primary_phone_number', 
                'applicants.secondary_phone_number', 
                'applicants.verification_status', 
                'applicants.note', 
                'applicants.approval_status', 
                'applicants.approval_note', 
                'applicants.stock_checking_status',  
                'applicants.created_at',  
                'applicants.updated_at',  
                'agency.location_district_code',   
                'agency.location_subdistrict_code',   
                'agency.location_village_code',   
                'agency.agency_type',   
                'applicants.applicant_name',
                'applicants.created_at as letter_date',
                DB::raw('"Belum Ada Surat Keluar" as status') 
                )
                ->with([
                    'masterFaskesType' => function ($query) {
                        return $query->select(['id', 'name']);
                    },
                    'agency' => function ($query) {
                        return $query;
                    },
                    'letter' => function ($query) {
                        return $query->select(['id', 'agency_id', 'letter']);
                    },
                    'city' => function ($query) {
                        return $query->select(['id', 'kemendagri_provinsi_nama', 'kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
                    },
                    'subDistrict' => function ($query) {
                        return $query->select(['id', 'kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
                    },
                    'village' => function ($query) {
                        return $query->select(['id', 'kemendagri_desa_kode', 'kemendagri_desa_nama']);
                    }
                ])
                ->join('agency', 'agency.id', '=', 'applicants.agency_id')
                ->findOrFail($id);
                $find = RequestLetter::where('applicant_id', $data->id)->first();
                $data->status = $find ? 'Ada Surat Keluar' : $data->status;
                $data->letter_date = date('Y-m-d', strtotime($data->letter_date));
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return response()->format(200, 'success', $data);
    }
}
