<style>
   .dz-message {
     text-align: left!important;
     margin: 2rem!important;
   }


</style>
<html>
 <head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.11.5/datatables.min.css"/>
 </head>
 <body>
  <div class="container" style="padding: 35px;">
      
        
      <div class="panel panel-default">
        <div class="panel-body row ">
            <div class="col-sm-8">
                <form id="dropzoneForm" class="dropzone" action="{{route('upload')}}" style="border: 0; min-height: 0!important; padding: 0!important;">
                    @csrf
                </form>
            </div>
            <div class="col-md-4 offset-md-3" style="margin-top: 1rem; text-align: right; padding-right: 3rem;">
                <button type="button" class="btn btn-info" id="submit-all">Upload</button>
            </div>
        </div>
      </div>
      <br />
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Uploaded Image</h3>
        </div>
        <div class="panel-body" id="uploaded_image">
          <table id="uploadTable" class="table w-100">
              <thead style="background-color: gainsboro">
                <tr>
                  <th>Time</th>
                  <th>File Name</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
              </div>
          </table>
        </div>
      </div>
    </div>
 </body>
</html>

<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.11.5/datatables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script type="text/javascript">

  Dropzone.options.dropzoneForm = {
    autoProcessQueue : false,
    acceptedFiles : ".csv",

    init:function(){
      var submitButton = document.querySelector("#submit-all");
      myDropzone = this;

      submitButton.addEventListener('click', function(){
        myDropzone.processQueue();
      });

      this.on("complete", function(){
        if(this.getQueuedFiles().length == 0 && this.getUploadingFiles().length == 0)
        {
          var _this = this;
          _this.removeAllFiles();
        }
        // load_images();
      });

    }
  };

  var table = $('#uploadTable').DataTable({
    destroy: true,
    "paging": false,
    "info": false,
    processing: false,
    serverSide: true,
    "ajax": {
        url: "{{ url('history') }}",
        method: "GET",
        "datatype": "json",
        "dataSrc": function ( json ) {
          var data = [];
          json.data.forEach(item => {
            var stillUtc = moment.utc(item.time).toDate();
            var local = moment(stillUtc).local().format('YYYY-MM-DD HH:mm:ss');
            data.push([local, item.name, item.status])
          })
          return data;
        }
    }
  });
  
  setInterval( function () {
      table.ajax.reload( null, false );
  }, 6000 );

</script>