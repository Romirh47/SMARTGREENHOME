 <!-- Card Sensor -->
 <div class="container">
     <div class="row mb-4">
         <div class="col-12 mb-4">
             <h2 class="text-center mb-4">SENSOR</h2>
         </div>

         @foreach ($sensors as $sensor)
             @php
                 $latestValue = $sensor->sensorData->sortByDesc('created_at')->first();
                 $value = $latestValue ? $latestValue->value : 'NaN';
                 $type = $sensor->type;
             @endphp
             <div id="sensor-{{ $sensor->id }}" class="col-lg-3 col-md-4 col-sm-6 mb-4">
                 <div class="card bg-success text-light">
                     <div class="card-header text-center">
                         <h5 class="font-weight-bold">{{ strtoupper($sensor->name) }}</h5>
                     </div>
                     <div class="card-body bg-light text-dark d-flex flex-column justify-content-between">
                         <div class="text-center">
                             <div class="card p-3">
                                 <h2 class="card-title mb-0">{{ strtoupper($value) }}</h2>
                             </div>
                         </div>
                         <div class="text-center mt-auto">
                             <p class="card-text mb-0"> {{ strtoupper($type) }}</p>
                         </div>
                     </div>
                 </div>
             </div>
         @endforeach
     </div>
 </div>

 @push('scripts')
     <script>
         $(document).ready(function() {
             function updateSensorData() {
                 $.ajax({
                     url: '/api/sensor-values', // Ganti dengan endpoint API yang sesuai
                     method: 'GET',
                     success: function(data) {
                         data.forEach(sensor => {
                             const sensorCard = $(`#sensor-${sensor.id}`);
                             if (sensorCard.length) {
                                 sensorCard.find('.card-title').text(sensor.value.toUpperCase());
                                 sensorCard.find('.card-text').text(sensor.type.toUpperCase());
                             }
                         });
                     },
                     error: function(error) {
                         console.error('Error saat mengambil data sensor:', error);
                     }
                 });
             }

             // Panggil fungsi update setiap 1000 ms (1 detik)
             setInterval(updateSensorData, 1000);
         });
     </script>
 @endpush
