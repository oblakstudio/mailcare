<template>
  <div>

    <nav class="level">

      <div class="level-left">
        <div class="tabs is-toggle">
          <ul>
            <li v-for="bodyType in bodyTypes" :class="classesLink(bodyType)" @click="getContent(bodyType.accept)" :data-label="bodyType.label">
              <a>
                <span class="icon is-small">
                  <i :class="classes(bodyType)"></i>
                </span>
                <span v-text="bodyType.label"></span>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="level-right is-small">
        <nav class="panel">
          <p class="panel-heading">
            {{ this.email.attachments.length }} attachments
          </p>
          <a v-for="attachment in this.email.attachments" class="panel-block" v-bind:href="'/api/emails/' + email.id + '/attachments/' + attachment.id">
            <span class="panel-icon">
              <i :class="classesContentType(attachment.content_type)"></i>
            </span>
            {{ attachment.file_name }} ({{ attachment.size_for_human }})
          </a>
        </nav>
      </div>

    </nav>

    <div>
      <i-frame class="my-frame" name="iframe-body">
        <div v-html="body"></div>
      </i-frame>
    </div>

  </div>

</template>

<script>

    export default {

        props: ['id', 'email'],

        data() {
          return {
            bodyTypes: [
                {label: 'Html', icon: 'fa-image', accept: 'text/html', br: false},
                {label: 'Text', icon: 'fa-align-left', accept: 'text/plain', br: true},
                {label: 'Raw', icon: 'fa-sticky-note', accept: 'message/rfc2822', br: true}
                ],
            body: null,
          }
        },

        mounted() {
            this.getContent();
        },

        methods: {

          classesContentType(contentType)
          {
            if (contentType.startsWith("image"))
            {
              return ['fa', 'fa-file-image']
            }
            else if (contentType.startsWith("audio"))
            {
              return ['fa', 'fa-file-audio']
            }
            else if (contentType.startsWith("text"))
            {
              return ['fa', 'fa-file-alt']
            }
            else if (contentType.startsWith("video"))
            {
              return ['fa', 'fa-file-video']
            }
            else if (contentType.includes("excel") || contentType.includes("spreadsheet"))
            {
              return ['fa', 'fa-file-excel']
            }
            else if (contentType.includes("powerpoint") || contentType.includes("presentation"))
            {
              return ['fa', 'fa-file-powerpoint']
            }
            else if (contentType.includes("zip"))
            {
              return ['fa', 'fa-file-archive']
            }
            else if (contentType.includes("pdf"))
            {
              return ['fa', 'fa-file-pdf']
            }
            else if (contentType.includes("word"))
            {
              return ['fa', 'fa-file-word']
            }
            else
            {
              return ['fa', 'fa-file']
            }
          },

          classesLink(bodyType)
          {
            var isDisabled = false
            if (bodyType.label == 'Html')
            {
              isDisabled = !this.email.has_html;
            }
            else if (bodyType.label == 'Text')
            {
              isDisabled = !this.email.has_text;
            }

            if (isDisabled)
              return [bodyType.isActive ? 'is-active' : '', 'is-disabled']
            else
              return [bodyType.isActive ? 'is-active' : '']
          },

          classes(bodyType) {
            return ['fa', bodyType.icon]
          },

          getContent(accept = 'text/html,text/plain,message/rfc2822') {

              axios({
                method:'get',
                url:'/api/emails/' + this.id,
                headers: {'Accept': accept}
              })
              .then(function (response) {
                this.bodyTypes.forEach(bodyType => {
                  if (response.headers['content-type'].indexOf(bodyType.accept) > -1)
                  {
                    if (bodyType.br)
                    {
                      this.body = response.data.replace(/(\r\n|\n|\r)/gm, "<br>");
                    }
                    else
                    {
                      this.body = response.data
                    }
                    bodyType.isActive = true
                  }
                  else
                  {
                    bodyType.isActive = false
                  }
                })

              }.bind(this));

          },
        }
    }
</script>


<style scoped>

  .my-frame {
    min-height: 100%;
    border: 1px solid #d6dbe1;
    margin: 20px auto;
    height: 800px;
    width: 100%;

  }

  .is-disabled{
    pointer-events: none;
    opacity: .65;
  }
</style>

