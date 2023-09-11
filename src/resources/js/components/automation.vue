
<template>
  <form name="automationForm">
	<div>
		<div class="card">
      <header class="card-header">
        <p class="card-header-title">
          {{ automation.title }}&#8239;
        </p>
        <span v-if="automation.in_error" class="card-header-icon">
          <span class="tag is-danger">
            Last run in error
          </span>
        </span>
        <span v-if="automation.emails_received" class="card-header-icon">
          <span class="tag is-success">
            {{ automation.emails_received }} emails received
          </span>
        </span>
        <span v-else="automation.emails_received" class="card-header-icon">
          <span class="tag is-light">
            0 email received
          </span>
        </span>
        <a href="#" class="card-header-icon" dusk='open-button' aria-label="more options" @click.prevent='toggleOpen'>
          <span class="icon">
            <i class="fas fa-angle-down" aria-hidden="true" v-if="!open"></i>
            <i class="fas fa-angle-up" aria-hidden="true" v-else="!open"></i>
          </span>
        </a>
      </header>
      <div class="card-content" v-if="open">
        <div class="content">
          <div class="columns">
            <div class="column">
              <p class="help" v-if="selectedTypeOfAction === 'webhook'">
              We’ll send a POST request to the URL below with details of any emails received. You can also specify which data format you’d like to receive (JSON or raw).
            </p>
              <p class="help" v-if="selectedTypeOfAction === 'forwarding'">
              We’ll forward the email to the email below.
            </p>
            </div>
          </div>
          <div class="columns">
            <div class="column">
              <div>
                <div class="field">
                  <label class="label">Title</label>
                  <div class="control">
                    <input class="input" type="text" placeholder="Text input" required 
                    v-model="automation.title" 
                    dusk="title-field"
                    :disabled="!editable">
                  </div>
                </div>

                <div class="field">
                  <label class="label">Sender</label>
                  <div class="control has-icons-left has-icons-right">
                    <input class="input" placeholder="Email input" 
                    v-model="automation.sender" 
                    dusk="sender-field"
                    :disabled="!editable">
                    <span class="icon is-small is-left">
                      <i class="fas fa-envelope"></i>
                    </span>
                  </div>
                </div>

                <div class="field">
                  <label class="label">Inbox</label>
                  <div class="control has-icons-left has-icons-right">
                    <input class="input" placeholder="Email input" 
                    v-model="automation.inbox" 
                    dusk="inbox-field"
                    :disabled="!editable">
                    <span class="icon is-small is-left">
                      <i class="fas fa-envelope"></i>
                    </span>
                  </div>
                </div>

                <div class="field">
                  <label class="label">Subject</label>
                  <div class="control">
                    <input class="input" type="text" placeholder="Text input" 
                    v-model="automation.subject" 
                    dusk="subject-field"
                    :disabled="!editable">
                  </div>
                </div>

                <div class="field">
                  <div class="control">
                    <label class="checkbox">
                      <input type="checkbox" 
                      v-model="automation.has_attachments" 
                      dusk="attachments-checkbox"
                      :disabled="!editable">
                      Has attachments
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Type of action</label>
                <div class="control">
                   <select 
                   v-model="selectedTypeOfAction"
                   required
                   dusk="type-of-action-field"
                   :disabled="this.automation.id" >
                    <option v-for="typeOfAction in this.typeOfAction"
                      :value="typeOfAction.value"
                      >{{ typeOfAction.label }}
                    </option>
                  </select> 
                </div>
              </div>

              <div class="field" v-if="selectedTypeOfAction === 'webhook'">
                <label class="label">Url</label>
                <div class="control">
                  <input class="input" type="url" 
                  placeholder="https://example.com/trigger-email-received" 
                  required 
                  v-model="automation.action_url" 
                  dusk="url-field"
                  :disabled="!editable">
                </div>
              </div>
              <div class="field" v-if="selectedTypeOfAction === 'forwarding'">
                <label class="label">Forward To</label>
                <div class="control">
                  <input class="input" type="email" 
                  placeholder="notification@example.com" 
                  required 
                  v-model="automation.action_email" 
                  dusk="email-field"
                  :disabled="!editable">
                </div>
              </div>

              <div class="field" v-if="selectedTypeOfAction === 'webhook'">
                <label class="label">Secret Token</label>
                <div class="control">
                  <input class="input" type="text" placeholder="Text input" 
                  v-model="automation.action_secret_token" 
                  dusk="secret-field"
                  :disabled="!editable">
                </div>
                <p class="help">Use this token to validate received payloads. It will be sent with the request in the X-MailCare-Secret-Token HTTP header.</p>
              </div>

              <div class="field" v-if="selectedTypeOfAction === 'webhook'">
                <div class="control">
                  <label class="checkbox">
                    <input type="checkbox"
                    v-model="automation.post_raw"
                    dusk="raw-checkbox"
                    :disabled="!editable">
                    Post the raw, full MIME message
                  </label>
                </div>
              </div>

              <div class="field">
                <div class="control">
                  <label class="checkbox">
                    <input type="checkbox"
                    v-model="automation.action_delete_email"
                    dusk="delete-checkbox"
                    :disabled="!editable">
                    Delete email after running this automation
                  </label>
                </div>
              </div>

                <div class="field">
                  <p class="help">
                    <strong>Filter rules:</strong>
                    <ul>
                      <li>- Each filter are case insensitive</li>
                      <li>- An empty filter match all values</li>
                      <li>- By default, "keyword" will match any values that contains keyword</li>
                      <li>- You can use regex, like "^keyword$" will only match "keyword"</li>
                      <li>- or "^Your .* invoice$" will match "Your October invoice"</li>
                      <li>- As we use regex, all these characters ^ $ \ | { } [ ] ( ) ? # ! + * . should be escaped with \</li>
                    </ul>
                  </p>
                </div>
            </div>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && !automation.id">
            <p class="control">
              <a class="button is-link" @click='createAutomation' dusk="create-button">
                Create
              </a>
            </p>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && automation.id && editable">
            <p class="control">
              <a class="button is-link" @click='saveAutomation' dusk="save-button">
                Save
              </a>
            </p>
            <p class="control">
              <a class="button is-danger" @click='deleteAutomation' dusk="delete-button">
                Delete
              </a>
            </p>
            <p class="control">
              <a class="button" @click='toggleEditable' dusk="cancel-button">
                Cancel
              </a>
            </p>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && automation.id && !editable">
            <p class="control">
              <a class="button is-link" @click='toggleEditable' dusk="edit-button">
                Edit
              </a>
            </p>
          </div>

        </div>
      </div>
    </div>
    <br>
  </div>
  </form>
</template>

<script>
	export default {

    props: {
      dataAutomation: {
        type: Object,
        default: () => ({
          title: 'New automation',
          sender: '',
          inbox: '',
          subject: '',
          has_attachments: false,
          action_url: '',
          action_email: '',
          action_secret_token: '',
          action_delete_email: false,
          post_raw: false,
          emails_received: 0,
        })
      },
      dataTypeOfAction: {
        type: Array,
      },
      dataOpen: {
        type: Boolean,
        default: false,
      },
      dataEditable: {
        type: Boolean,
        default: false,
      },
    },

    data() {
      return {
        automation: this.dataAutomation,
        typeOfAction: this.dataTypeOfAction,
        selectedTypeOfAction: null,
        open: this.dataOpen,
        editable: this.dataEditable,
      }
    },

    mounted() {
      if (this.automation.id) {
        if (this.automation.action_email) {
          this.selectedTypeOfAction = 'forwarding'
        }
        else {
          this.selectedTypeOfAction = 'webhook'
        }
      }
    },

    methods: {

      toggleOpen() {
        this.open = !this.open
      },

      toggleEditable() {
        this.editable = !this.editable
      },

      createAutomation() {
        axios.post('/api/automations', this.automation).then((response) => {
          this.$emit('automation-created', this.automation)
        })
      },

      saveAutomation() {
        axios.put('/api/automations/' + this.automation.id, this.automation).then((response) => {
          this.editable = false
        })
      },

      deleteAutomation() {
        axios.delete('/api/automations/' + this.automation.id, this.automation).then((response) => {
          this.$emit('automation-deleted', this.automation)
        })
      }
    }

  }
</script>