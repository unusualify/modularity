<template>
  <div>
    <form class="filter" :class="{ 'filter--opened' : opened, 'filter--single' : !withNavigation, 'filter--withHiddenFilters' : withHiddenFilters }" @submit.prevent="submitFilter" ref="form">
      <div class="filter__inner">
        <div class="filter__navigation"><slot name="navigation"></slot></div>

        <div class="filter__search">

            <!-- <input type="search" class="form__input form__input--small" name="search" :value="searchValue" :placeholder="placeholder" @input="onSearchInput" /> -->
            <v-text-field
                type="search"
                class="form__input form__input--small v-filter__search__input"
                name="search"
                :modelValue="filterState.search"
                @update:modelValue="updateSearch"
                :placeholder="placeholder"
                variant="outlined"
                hide-details="true"
                />
            <ue-btn
                class="filter__toggle"
                variant="outlined"
                @click="toggleFilter"
                v-if="withHiddenFilters"
                :aria-expanded="opened ?  'true' : 'false'"

                fab
                >
                {{ $trans('filter.toggle-label', 'Filter') }}
                <span class="float-right" v-svg symbol="dropdown_module"></span>
            </ue-btn>
          <slot name="additional-actions"></slot>
          <!-- Fix for Safari: the hidden submit button enables form submission by pressing Enter... -->
          <!-- <ue-btn class="visually-hidden" variant="outlined" aria-hidden="true" type="submit">{{ $trans('filter.apply-btn', 'Apply') }}</ue-btn> -->
        </div>
      </div>
      <transition :css='false' :duration="275" @before-enter="beforeEnter" @enter="enter" @before-leave="beforeLeave" @leave="leave">
        <div class="filter__more" v-show="opened" v-if="withHiddenFilters" :aria-hidden="!opened ? true : null" ref="more">
          <div class="filter__moreInner" ref="moreInner">
            <slot name="hidden-filters"></slot>
            <ue-btn variant="outlined" type="submit">{{ $trans('filter.apply-btn', 'Apply') }}</ue-btn>
            <ue-btn v-if="clearOption" variant="elavated" type="button" @click="clear">{{ $trans('filter.clear-btn', 'Clear') }}</ue-btn>
          </div>
        </div>
      </transition>
    </form>
    <slot></slot>

  </div>
  </template>

<script>
import { getCurrentInstance } from 'vue'
import debounce from 'lodash-es/debounce'
import FormDataAsObj from '@/utils/formDataAsObj.js'

export default {
  name: 'UEFilter',
  props: {
    initialSearchValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default () {
        return getCurrentInstance().appContext.config.globalProperties.$trans('filter.search-placeholder', 'Search')
      }
    },
    closed: {
      type: Boolean,
      default: false
    },
    clearOption: {
      type: Boolean,
      default: false
    },
    filterState: {
      type: Object,
      required: true
    }
  },
  emits: ['update:filterState', 'submit', 'clear'],
  data: function () {
    return {
      openable: !this.closed,
      open: false,
      withHiddenFilters: true,
      withNavigation: true,
      searchValue: this.initialSearchValue,
      transitionTimeout: null
    }
  },
  computed: {
    opened: function () {
      return this.open && this.openable
    }
  },
  watch: {
    closed: function () {
      this.openable = !this.closed
    },
    initialSearchValue: function () {
      this.searchValue = this.initialSearchValue
    }
  },
  methods: {
    getHeight: function () {
      // Retrieve height from more inner container
      return this.$refs.moreInner.clientHeight
    },
    beforeEnter: function (el) {
      el.style.height = '0px'
      el.style.overflow = 'hidden'
    },
    enter: function (el, done) {
      // Reset height.
      this.resetHeight()

      // Delete timeout if exists.
      if (this.transitionTimeout) {
        clearTimeout(this.transitionTimeout)
      }

      // Set timeout.
      this.transitionTimeout = setTimeout(() => {
        el.style.overflow = 'visible'
      }, 275)

      // Add resize event.
      window.addEventListener('resize', this._resize, false)
    },
    beforeLeave: function (el) {
      // Delete timeout if exists.
      if (this.transitionTimeout) {
        clearTimeout(this.transitionTimeout)
      }

      // Reset height.
      this.resetHeight()

      // Hide content.
      el.style.overflow = 'hidden'

      // Remove resize event.
      window.removeEventListener('resize', this._resize)
    },
    leave: function (el, done) {
      el.style.height = '0px'
    },
    toggleFilter: function () {
      this.openable = true
      this.open = !this.open
    },
    submitFilter: function () {
      const formData = FormDataAsObj(this.$refs.form)
      this.$emit('submit', formData)
    },
    onSearchInput: function (val) {
      this.searchValue = val
    },
    clear: function () {
      this.searchValue = ''
      this.$emit('clear')
    },
    resetHeight: function () {
      // Return if ref is not set.
      if (!this.$refs.more) return

      // Set height to the container.
      this.$refs.more.style.height = this.getHeight() + 'px'
    },
    _resize: debounce(function () {
      this.resetHeight()
    }, 50),
    updateSearch(value) {
      this.$emit('update:filterState', { ...this.filterState, search: value });
    },
  },
  beforeMount: function () {
    if (!this.$slots.navigation) this.withNavigation = false
    if (!this.$slots['hidden-filters']) this.withHiddenFilters = false
  }
}
</script>

<style lang="scss" scoped>

  .filter__inner {
    display: flex;
    justify-content: space-between;
  }

  .filter__search, .filter__navigation {
    padding:20px 0;
    white-space: nowrap;
  }
  .filter__search {
    display:flex;
    align-items: center;
    .v-filter__search__input {
      display:inline-block;
      width:20vw;
      max-width:300px;
    }

    .icon {
      position:relative;
      top:-2px;
      margin-left:9px;
    }

    div {
      display:inline-block;

      button, a {
        vertical-align: middle;
      }

      input, button, a {
        margin-left:15px;
      }
    }
  }

  /* variant when filter has hidden filters on small screens */
  @include breakpoint(xsmall) {
    .filter--withHiddenFilters {
      .filter__inner {
        display:block;
      }

      .filter__search {
        display:flex;

        input {
          flex-grow:1;
        }
      }
    }
  }

  .filter__more {
    transition: height 0.275s ease;
    overflow: hidden;
  }

  .filter__moreInner {
    padding: 20px 0 0 0;
    border-top:1px solid $color__border;

    button {
      margin-right: 10px;
      margin-bottom: 20px;
    }
  }

  @include breakpoint('small+') {
    .filter__moreInner {
      display: flex;
      flex-flow: row wrap;
    }
  }

  .filter__toggle {
    position: relative;
    padding-right:  20px + 20px !important;
    margin-left: 15px !important;

    .icon {
      transition: all .2s linear;
      transform: rotate(0deg);
      position: absolute;
      right: 20px;
      top: 50%;
      margin-top: -3px;
    }
  }

  /* Opened filters */
  .filter--opened {
    .filter__toggle .icon {
      transform: rotate(180deg);
    }
  }

  .filter__navigation {
      padding: 20px 0;

  }

  .filter--single {
    .filter__navigation {
      display: none;
    }
  }
</style>

<style lang="scss">

  .filter {
    .filter__moreInner {
      .input {
        margin-top: 0;
        margin-bottom: 20px;
      }
    }

    @include breakpoint('small+') {
      .filter__moreInner {
        .input {
          margin-top: 0;
          margin-right: 20px;
        }

        > div {
          display: flex;
          flex-flow: row wrap;
        }

        > div > * {
          margin-right: 20px;
        }
      }
    }
  }
</style>
