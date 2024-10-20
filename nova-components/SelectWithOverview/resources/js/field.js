import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-select-with-overview', IndexField)
  app.component('detail-select-with-overview', DetailField)
  app.component('form-select-with-overview', FormField)
})
