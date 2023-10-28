<script>

    import moment from 'moment';

    export default {

        render(h) {
          return  h('iframe', {
            on: { load: this.renderChildren }
          })
        },

        beforeUpdate() {
          //freezing to prevent unnessessary Reactifiation of vNodes
          if (this.iApp) {
            this.iApp.children = Object.freeze(this.$slots.default)
          }
        },

        methods: {
          renderChildren() {
            const children = this.$slots.default
            const body = this.$el.contentDocument.body      
            const el = document.createElement('DIV') // we will mount or nested app to this element
            body.appendChild(el)

            const iApp = new Vue({
              name: 'iApp',
              //freezing to prevent unnessessary Reactifiation of vNodes
              data: { children: Object.freeze(children) }, 
              render(h) {
                return h('div', this.children)
              },
            })

            iApp.$mount(el) // mount into iframe

            this.iApp = iApp // cache instance for later updates
          }
        }
    }
</script>
