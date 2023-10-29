<template>

<div>
<!-- Main container -->
<nav class="level">
  <!-- Left side -->
  <div class="level-left">
    <div class="level-item">
      <p class="subtitle is-5">
        <strong>{{ totalCount }}</strong> emails
      </p>
    </div>
    <div class="level-item">
      <div class="field has-addons">
        <p class="control">
          <input class="input" type="text" placeholder="Search..." v-model="keywords" v-on:input="filterByKeywords()">
        </p>
      </div>
    </div>
  </div>

  <!-- Right side -->
  <div class="level-right">

    <div>
    <p>
      <div class="tabs is-small is-toggle">
        <ul>
          <li :class="classes(!this.filteredBy)">
            <a @click="filterBy()">
              <span class="icon is-small"><i class="fas fa-asterisk"></i></span>
              <span>All</span>
            </a>
          </li>
          <li :class="classes(this.filteredByUnread())">
            <a @click="filterBy('unread')">
              <span class="icon is-small"><i class="fas fa-circle"></i></span>
              <span>Unread</span>
            </a>
          </li>
          <li :class="classes(this.filteredByFavorite())">
            <a @click="filterBy('favorite')">
              <span class="icon is-small"><i class="fas fa-heart"></i></span>
              <span>Favorite</span>
            </a>
          </li>
        </ul>
      </div>
    </p>
    <p v-if="this.inbox">
    Filtered by inbox:
    <span class="tag is-primary">
      {{ inbox }}
      <button @click="removeFilter()" class="delete is-small"></button>
    </span>
    </p>
    <p v-if="this.sender">
    Filtered by sender:
    <span class="tag is-primary">
      {{ sender }}
      <button @click="removeFilter()" class="delete is-small"></button>
    </span>
    </p>
    </div>
  </div>

</nav>

  <div v-if="totalCount">
    <table class="table table is-fullwidth" v-if="totalCount">
      <thead>
        <tr>
          <th><abbr title="Received date">Date</abbr></th>
          <th>Subject</th>
          <th>Sender</th>
          <th>Inbox</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="email in emails">
          <td>{{ email.created_at | ago }}</td>
          <td>
            <span class="icon is-small">
              <i v-if="email.favorite" class="fa fa-heart"></i>
              <i v-else-if="!email.read" class="fa fa-circle"></i>
            </span>
            <a v-bind:href="'/emails/' + email.id" v-bind:id="email.id">{{ email.subject }}</a>
          </td>
          <td>{{ email.sender.email }}</td>
          <td>{{ email.inbox.email }}</td>
        </tr>
      </tbody>
    </table>

    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
      <a class="pagination-previous" v-if="hasPreviousPage()" @click="loadPreviousPage()">Previous</a>
      <a class="pagination-next" v-if="hasNextPage()" @click="loadNextPage()">Next page</a>
      <ul class="pagination-list">
        <li v-for="page in getPages()">

          <a v-if="page == meta.current_page"
            class="pagination-link is-current"
            aria-current="page" @click="goToPage(page)">{{ page }}</a>
          <span v-else-if="page == '...'"
            class="pagination-ellipsis">&hellip;</span>
          <a class="pagination-link" @click="goToPage(page)" v-else>{{ page }}</a>
        </li>
      </ul>
    </nav>
    </div>

    <article class="message" v-else-if="totalCount == 0">
      <div class="message-header" style="justify-content: flex-start;">
        <span class="icon is-medium"><i class="far fa-meh"></i></span>
        <span>Oops! No emails matched your query.</span>
      </div>
      <div class="message-body">
      <p>A few things that might help:</p>
      <p>- Use MailCare to receive your first email</p>
      <p>- Remove your search query filter...</p>
      <p>- Change your query filter Unread/Favorite to All</p>
      <p>- Remove your query filter Inbox/Sender</p>
      </div>
    </article>

    <div>
    </div>
    <div>
    </div>

  </div>
</template>

<script>

    import moment from 'moment';

    export default {

        props: ['inbox', 'sender'],

        data() {
          return {
            emails: [],
            totalCount: null,
            keywords: null,
            filteredBy: null,
            pageRequested: null,
            cancelSource: null,
          }
        },

        filters: {
          ago(date) {
            return moment(date).fromNow();
          }
        },

        mounted() {
          this.getEmails()
        },

        methods: {

          hasPreviousPage() {
            return this.meta.current_page > 1;
          },

          loadPreviousPage() {
            this.goToPage(this.meta.current_page - 1)
          },

          hasNextPage() {
            return this.meta.current_page < this.meta.last_page;
          },

          loadNextPage() {
            this.goToPage(this.meta.current_page + 1)
          },

          goToPage(page)
          {
            this.pageRequested = page
            this.getEmails()
          },

          getPages() {

            var pages = []
            var i

            if (this.meta.current_page - 3 > 1)
            {
              pages.push(1)
            }
            if (this.meta.current_page - 3 > 2)
            {
              pages.push('...')
            }

            for (i = this.meta.current_page - 3; i <= this.meta.current_page + 3; i++) {
                if (i > 0 && i <= this.meta.last_page) {
                  pages.push(i)
                }
            }
            if (this.meta.last_page > this.meta.current_page + 3 + 1)
            {
              pages.push('...')
            }

            if (this.meta.last_page > this.meta.current_page + 3)
            {
              pages.push(this.meta.last_page)
            }

            return pages

          },

          removeFilter() {
            window.location = "/"
          },

          filteredByUnread() {
            return this.filteredBy == 'unread';
          },

          filteredByFavorite() {
            return this.filteredBy == 'favorite';
          },

          classes(filtered) {
            return [filtered ? 'is-active' : ''];
          },

          filterBy(filtered = null) {
            this.pageRequested = null
            this.filteredBy = filtered
            this.getEmails()
          },

          filterByKeywords()
          {
            this.pageRequested = null
            this.getEmails()
          },

          getEmails() {
            if (this.cancelSource){
              this.cancelSource.cancel()
            }
            this.cancelSource = axios.CancelToken.source()
            axios.get('/api/emails', { cancelToken: this.cancelSource.token, params: {
              'inbox': (this.inbox ? this.inbox : null),
              'sender': (this.sender ? this.sender : null),
              'search': (this.keywords ? this.keywords : null),
              'unread': (this.filteredByUnread() ? '1' : null),
              'favorite': (this.filteredByFavorite() ? '1' : null),
              'page': (this.pageRequested ? this.pageRequested : null),
            }}).then(function(response) {
              this.emails = response.data.data
              this.meta = response.data.meta
              this.totalCount = response.data.meta.total
              this.cancelSource = null
            }.bind(this));

          },
        }
    }
</script>
