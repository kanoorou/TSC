// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  routeRules: {
    "/": { prerender: true }
  },
  css: ['~/assets/css/common.css'],
  nitro:{
    prerender: {
        // failOnError: false,
        crawlLinks: false // change when all pages ready

        },
  },
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  app: {
    head: {
      titleTemplate: '%s -TSC 全合成グランプリ-',
      htmlAttrs: { lang: 'ja' },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },],
      link: [
        {rel:'shortcut icon', type: 'image/png', href:'/assets/img/logo.png'},{ rel: 'stylesheet', href: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', integrity:'sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM',crossorigin:'anonymous' }],
      script: [{ src: '/assets/js/common.js' },{src:'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', integrity:'sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz',crossorigin:'anonymous'}]
    } 
  }
  
})
