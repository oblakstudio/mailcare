<template>
  <div class="card" v-if="email">
    <div class="card-content">
      <nav class="level">
        <div class="level-left">
          <div>
            <h1 class="title is-spaced">{{ email.subject }}</h1>
            <h2 class="subtitle is-5" v-if="email.created_at">
              From <a v-bind:href="'/senders/' + email.sender.email">{{ email.sender.email }}</a> 
              to <a v-bind:href="'/inboxes/' + email.inbox.email">{{ email.inbox.email }}</a>
            </h2>
          </div>
        </div>
        <div class="level-right has-text-centered">
          <div>
            <p class="heading">{{ email.created_at | ago }}</p>
            <p class="title">
              <button dusk="favorite-button" :class="classes" @click='toggle'>
                <span class="icon is-small"><i class="fas fa-heart"></i></span>
              </button>
            </p>
          </div>
        </div>
      </nav>
      <hr>
      <show-email-body :id="id" :email="email"></show-email-body>
      <div class="buttons has-addons is-right">
        <a dusk="delete-button" class="button is-danger is-outlined" :class="classesDeleting" @click="deleteEmail">
          <span class="icon is-small">
            <i class="fas fa-times"></i>
          </span>
          <span>Delete</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script>

    import moment from 'moment'

    export default {

        props: ['id'],

        data() {
          return {
            email: null,
            isDeleting: false,
          }
        },

        computed: {
          classes() {
            return ['button', this.email.favorite ? 'is-primary' : '']
          },
          classesDeleting() {
            return [this.isDeleting ? 'is-loading' : '']
          }
        },

        filters: {
          ago(value) {
            return moment(value).fromNow();
          }

        },

        mounted() {
          axios({
            method:'get',
            url:'/api/emails/' + this.id,
            headers: {'Accept': 'application/json'}
          })
          .then(response => this.email = response.data.data);
        },

        methods: {
          toggle() {
            if (this.email.favorite) {
              axios.delete('/api/emails/' + this.id + '/favorites')
              this.email.favorite = false;
            } else {
              axios.post('/api/emails/' + this.id + '/favorites')
              this.email.favorite = true;

            }
          },
          deleteEmail() {
              this.isDeleting = true
              axios.delete('/api/emails/' + this.id)
              window.location = "/"
          }
        }
    }

</script>
