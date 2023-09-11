<template>
	<div>
		<nav class="level">
			<div class="level-left">
				<p class="level_item subtitle is-5">
					<strong v-text="automations.length"></strong> automations
				</p>
			</div>
			<div class="level-right">
				<a class="level-item button is-primary" 
					dusk="close-create-button" 
					@click='toggleCreateForm' 
					v-if="showCreateForm">Close</a>
				<a class="level-item button is-primary" 
					dusk="open-create-button" 
					@click='toggleCreateForm' 
					v-else>Create</a>
			</div>
		</nav>

		<automation 
			v-on:automation-created="created()"
			v-if="showCreateForm" 
			:data-type-of-action="getTypeOfAction"
			:data-open='true' 
			:data-editable='true'>
		</automation>
		<automation v-for="automation in automations" 
			v-on:automation-deleted="automationDeleted()"
			:data-type-of-action="getTypeOfAction"
			:key="automation.id" 
			:data-automation="automation"
			>
		</automation>
	</div>
</template>

<script>
	import automation from './automation.vue'

	export default {

		components: {
			automation,
		},

		props: {

	      forward: {
	        type: Boolean,
	        default: false,
	      },
		},

		data() {
			return {
				automations: [],
				showCreateForm: false,
			}
		},

		mounted() {

			this.getAutomations()
		},

		computed: {
			getTypeOfAction() {
				if(this.forward) {
					return [
						{'value': 'webhook', 'label': 'Webhook'},
						{'value': 'forwarding', 'label': 'Forwarding'}
						]

				} else {
					return [
						{'value': 'webhook', 'label': 'Webhook'},
						]
				}
			}
		},

		methods: {
			toggleCreateForm() {
				this.showCreateForm = !this.showCreateForm
			},

			created() {
				this.toggleCreateForm()
				this.getAutomations()

			},

			automationDeleted() {
				this.getAutomations()

			},

			getAutomations() {
				axios.get('/api/automations').then(function(response) {
					this.automations = response.data.data
				}.bind(this));
			}
		}

	}
</script>