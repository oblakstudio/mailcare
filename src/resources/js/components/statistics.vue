<template>

  <div>
    <nav class="level"> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Emails received</p> 
          <p class="title" v-text="meta.emails_received"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Inboxes created</p> 
          <p class="title" v-text="meta.inboxes_created"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Storage used</p> 
          <p class="title" v-text="meta.storage_used_for_human"></p> 
        </div> 
      </div> 
    </nav> 


<div class="container">
  <canvas id="emails-chart"></canvas>
  <canvas id="storage-chart"></canvas>
</div>

  </div>

</template>

<script>

    export default {

        data() {
          return {
            meta: [],
            data: [],
          }
        },

        filters: {
        },

        mounted() {
          axios.get('/api/statistics').then(function(response) {
            this.meta = response.data.meta
            this.data = response.data.data

            var ctxEmails = document.getElementById("emails-chart").getContext('2d');

            var emailsChart = new Chart(ctxEmails, {
                type: 'line',
                data: {
                  labels: _.map(this.data, 'created_at'),
                  datasets: [{
                    label: 'Emails deleted by date',
                    data: _.map(this.data, 'emails_deleted'),
                    borderColor: '#ff0000',
                    backgroundColor: '#ff4d4d'
                  },{
                    label: 'Emails received by date',
                    data: _.map(this.data, 'emails_received'),
                    borderColor: '#e65722',
                    backgroundColor: '#ee8b67'
                  }]
                },
            });

            var ctxStorage = document.getElementById("storage-chart").getContext('2d');

            var storageChart = new Chart(ctxStorage, {
                type: 'bar',
                options: {
                  scales: {
                    yAxes: [{
                      ticks: {
                          min: 0,
                          max: 100,
                        }
                    }]
                  }
                },
                data: {
                  labels: _.map(this.data, 'created_at'),
                  datasets: [{
                    label: '% Storage used by date',
                    data: _.map(this.data, 'cumulative_storage_used').map(x => x / this.meta.total_space * 100),
                    borderColor: '#1f77b4',
                    backgroundColor: '#7693eb'
                  }]
                },
            });

          }.bind(this));
        },

        methods: {
        }
    }
</script>
