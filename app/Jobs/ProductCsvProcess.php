<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



class ProductCsvProcess implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $header;
    public $data;

    public function __construct($data, $header)
    {
        $this->data = $data;
        $this->header = $header;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('any_key')->allow(2)->every(1)->then(function () {
            $headers = $this->tableName();
            foreach ($this->data as $record) {
                if (count($this->header) != count($record)) {
                    unset ($record);
                }else{
                    
                    
                    // Decode unwanted html entities
                    $record = array_map("html_entity_decode", $record);
                    
                    // clean and combine array header and value
                    $recordz = $this->clear_encoding_str(array_combine($this->header, $record));

                    // compare array key with key column from database
                    $select_field =  array_filter(
                        $recordz,
                        fn ($key) => in_array($key, $headers),
                        ARRAY_FILTER_USE_KEY
                    );
                    
                    Product::updateOrCreate($select_field);  
                }
            }
        }, function () {
            // Could not obtain lock; this job will be re-queued
            return $this->release(2);
        });
        
    }

    private function clear_encoding_str($value)
    {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $val) {
                $clean[$key] = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
            }
            return $clean;
        }
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    private function tableName(){
        $user= new Product;
    
        $table = $user->getTable();

        $columns = \DB::getSchemaBuilder()->getColumnListing($table);
        
        return $columns;
    }
}
