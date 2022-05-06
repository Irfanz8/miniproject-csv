<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\History;
use App\Jobs\ProductCsvProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Carbon\Carbon;

class UploadController extends Controller
{
    public function index(){
        return view('index');
    }
    public function import(Request $request){
        $request->file->getClientOriginalName();
        if( $request->has('file') ) {
            $csv    = file($request->file);
            $batch  = Bus::batch([])->name('Import Contacts')->dispatch();
            $data =  array_map('str_getcsv', $csv);
            $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', array_map('strtolower', $data[0]));
            unset($data[0]);

            $batch->add(new ProductCsvProcess($data, $header));

            History::updateOrCreate([
                'name' => $request->file->getClientOriginalName()
            ],[
                'time' => Carbon::now()->toDateTimeString(),
                'status' => 'pending',
                'job_id' => $batch->id
            ]);

            //opsi retrun 1
            return \Response::json([
                'code' => 200,
                'message' => 'success',
            ]);
            //opsi retrun 2
            // return History::All();
        }
        return \Response::json([
            'code' => 400,
            'message' => 'please upload csv file',
        ]);
    }

    public function getHistory(){
        $data = History::all();
        
        foreach ($data as $data) {
            $jobs = Bus::findBatch($data->job_id);
            $update = History::query();
            $update->where('job_id', $jobs->id);

            if ($jobs->pendingJobs == 1) {
                $update->update(['status' => "pending"]);
            } elseif($jobs->progress() < 100) {
                $update->update(['status' => "processing"]);
            } elseif($jobs->processedJobs() == 1 && $jobs->progress() == 100 && $jobs->finished() == 1){
                $update->update(['status' => "completed"]);
            } elseif($jobs->failedJobs){
                $update->update(['status' => "failed"]);
            }
            
        }
        return \Response::json([
            'code' => 200,
            'message' => 'success',
            'data' => History::all()
        ]);
    }
}
