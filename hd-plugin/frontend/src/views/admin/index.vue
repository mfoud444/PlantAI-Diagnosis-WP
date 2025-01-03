<script
    setup
    lang='ts'
>
import {
  NTabs, NTabPane
} from 'naive-ui'
import { t } from '@/locales';
import ListPlants from './plants/ListPlants.vue'

import Dashboard from './dashboard/index.vue'
import HistoryUser from './dashboard/HistoryUser.vue'
</script>

<template>
    <div class="px-4 py-4">
  <NTabs type="segment" animated>
    <NTabPane name="general" :tab="t('common.general')">
     <Dashboard/>
    </NTabPane>
    <NTabPane name="plants" :tab="t('dashboard.plants')">

  <ListPlants/>
    </NTabPane>
  

    <NTabPane name="historyUser" :tab="t('common.historyUsers')">
<HistoryUser/>
    </NTabPane>
  </NTabs>
</div>
</template>

