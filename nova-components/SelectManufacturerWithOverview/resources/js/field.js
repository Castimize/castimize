import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-select-manufacturer-with-overview', IndexField)
  app.component('detail-select-manufacturer-with-overview', DetailField)
  app.component('form-select-manufacturer-with-overview', FormField)
})
