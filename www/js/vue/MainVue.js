/* 
 * Main Vue.js app file
 * Posledna zmena(last change): 08.06.2020
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 */

Vue.component('slider', {
    props: {
        source: {
            type: String,
            required: true
        }
    },
    template: `<img :src="source" alt="Slider">`
})

Vue.component('autocomplete', {
    props: {
        source: {
            type: String,
            required: true,
        },
        choice: {
            type: String,
            required: true,
        },
        myred: String,
        placeholder: String,
        inputname: String
    },
    data: function () {
        return {
            searchquery: '',
            results: [],
            isOpen: false,
            isSearching: true,
            arrowCounter: -1,
        }
    },
    template: `
      <div class="autocomplete">
        <form autocomplete="off" class="form-inline" @submit.prevent><!--required for disable google chrome auto fill-->
          <input  type="search" 
                  :placeholder="placeholder"
                  :name="inputname"
                  class="form-control mr-sm-2"
                  aria-label="Search"
                  v-model="searchquery"
                  @input="autoComplete"
                  @keydown.down="onArrowDown"
                  @keydown.up="onArrowUp"
                  @keydown.enter="onEnter"
          >
          <button class="btn my-2 my-sm-0" type="submit"><i class="fas fa-binoculars" style="font-size: 2rem"></i></button>
          <div class="autocomplete-result" v-show="isOpen">
            <ul class="list-group">
              <li class="list-group-item text-secondary" v-show="isSearching">
                  <span v-show="searchquery.length > 2">
                      <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                      Hľadám...
                  </span>
                  <span v-show="searchquery.length < 3"> &nbsp; </span>
              </li>
              <li class="list-group-item"
                  v-for="(result, i) in results"
                  :key="result.id"
                  :class="{ 'is-active': i === arrowCounter }"
              >
                  <a :href="setLink(result.id)" :title="result[choice]" @click="onAClick"> {{ result[choice] }} </a>
              </li>
            </ul>
          </div>
        </form>
      </div>
    `,
    methods: {
        autoComplete() {
            this.$emit('autocomplete-start');
            this.results = [];
            if (this.searchquery.length > 0) {
                this.isOpen = true;
                this.isSearching = true;
            }
            if (this.searchquery.length > 2) {
                axios.get(this.source, {params: {[this.inputname]: this.searchquery}})
                     .then(response => {
                          this.results = [];
                          response.data.forEach(cl => this.results.push(cl))
                          this.isSearching = false;     
                     })
                    .catch((error) => {
                        console.log(error);
                    });
              
            }
        },
        setLink(id) {
            return this.myred + '/' + id;
        },
        onArrowDown() {
        //    if (this.arrowCounter < this.results.length - 1) {
        //        this.arrowCounter = this.arrowCounter + 1;
        //    }
        },
        onArrowUp() {
        //   if (this.arrowCounter > 0) {
        //        this.arrowCounter = this.arrowCounter - 1;
        //    }
        },
        onEnter() {
        //    this.setResult(this.results[this.arrowCounter]);
        //    this.arrowCounter = -1;
        },
        onAClick() {
          return true;
        },
        handleClickOutside(evt) {
            if (!this.$el.contains(evt.target)) {
                this.isOpen = false;
                this.arrowCounter = -1;
                this.searchquery = '';
            }
        }
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside)
    },
    destroyed() {
        document.removeEventListener('click', this.handleClickOutside)
    }
  })
  
  
  //for Tracy Debug Bar
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  
  var vm = new Vue({
      el: '#vueapp',
      data: function () {
          return {
              handle: null,
              param: null
          }
      },
      /*components: { autocomplete },*/
      /*methods: {},*/
  
      mounted: function () {
  //        this.handle = this.$el.getAttribute('data-handle-link');
  //        this.param = this.$el.getAttribute('data-handle-param');
      }
  });//.$mount('#autocomplete');   
