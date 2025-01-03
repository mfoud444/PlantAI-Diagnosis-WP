<script setup lang='ts'>
import { ref, h, onMounted, computed,reactive } from 'vue'
import {
  NSpace, NButton, NDataTable, DataTableBaseColumn,
  useDialog, NEmpty,NResult,
  DataTableRowKey, NModal,
  useMessage, DataTableFilterState, DataTableColumns,
} from 'naive-ui'
import Cards from './Cards.vue'
import { useDashboardStore} from '@/store'
import { t } from '@/locales';
import { useIconRender } from '@/hooks/useIconRender'
import { useBasicLayout } from '@/hooks/useBasicLayout';
import CardsPlants from '../plants/CardPlants.vue'
import { SvgIcon } from '@/components/common';
import AddDiseases from './AddDiseases.vue'
// import UpdateUniversity from './UpdateUnversity.vue'
const dashboardStore = useDashboardStore()
const { iconRender } = useIconRender()
const loadingActionDelete = ref(false)
const loadingActionEdit = ref(false)
const loading = ref(true)
const error_get = ref<boolean>(false)

const checkedRowKeysRef = ref<Array<string | number>>([])
const { isMobile } = useBasicLayout()
const dialog = useDialog()
const message = useMessage();
const rowEdit = ref<Dashboard.Diseases | null>(null);
const data = computed(() => dashboardStore.listDiseases)
const pageSize:number = 3
const pages = computed(() => 3)
const pagination = reactive({
  page: pages!,
  pageSize:pageSize,
  showSizePicker: true,
  // pageSizes: [3, 5, 7],
  onChange: (page: number) => {
    pagination.page = page
  },
  onUpdatePageSize: (pageSize: number) => {
    pagination.pageSize = pageSize
    pagination.page = 1
  }
})
function handleDeleteAction(row: Dashboard.Diseases) {
  const deleteDialog = dialog.warning({
    title: t('chat.deleteConfirmation'),
    content: t('chat.deleteConfirmationMessage'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      try {
        deleteDialog.loading = true
        await dashboardStore.deleteDiseasesAction(row.id!)
        message.success(t('chat.deleteSuccess'));
      } catch (error: any) {
        deleteDialog.loading = false
        message.error(t('chat.deleteFailed'));
      } finally {
        deleteDialog.loading = false
      }
    },
  });
}

async function handleUpdateUniversity(row: Dashboard.Diseases) {
    dashboardStore.showModelUpdatePlant= true;
  rowEdit.value = row;
}

const mainColumn1 = reactive<DataTableBaseColumn<Dashboard.Diseases>>({
  title: t('dashboard.plant'),
  key: 'plants',
  render(row: Dashboard.Diseases) {
    return h(
      CardsPlants,
      {
        row: dashboardStore.getPlantById(row.plantId!)
      },
    )
  },
})
// console.log(dashboardStore.getPlantById("1"))
const mainColumn2 = reactive<DataTableBaseColumn<Dashboard.Diseases>>({
  title: t('dashboard.disease'),
  key: 'diseases',
  render(row: Dashboard.Diseases) {
    return h(
      Cards,
      {
        row: row
      },
    )
  },
})



const columns = reactive<DataTableColumns<Dashboard.Diseases>>([
  // {
  //   type: 'selection',
  //   options: [
  //     'all',
  //     'none',
  //   ],

  // },
  mainColumn1,
  mainColumn2,
  {
    title: t('app.actions'),
    key: 'actions',
    align: 'center',
    width: 100,
    render(row: Dashboard.Diseases) {
      return h(
        'div',
        {
          class: 'flex gap-1'
        },
        [
          h(
            NButton,
            {
              strong: true,
              tertiary: true,
              size: 'small',
              loading: loadingActionEdit.value,
              style:"border-radius:100%",
              onClick: async () => {
                try {
                  await handleUpdateUniversity(row);
                } catch (error: any) {

                  console.error(t('common.updateFailed'), error.message);
                }
              }
            },
            { default: () => h(iconRender({ icon: 'fluent:edit-32-regular', color: 'blue' })) }
          ),

          h(
            NButton,
            {
              strong: true,
              tertiary: true,
              size: 'small',
              loading: loadingActionDelete.value,
              onClick: () => handleDeleteAction(row)
            },
            { default: () => h(iconRender({ icon: 'fluent:delete-32-regular', color: 'red' })) }
          ),

        ]
      );
    }
  },

]);



async function getDataAsync() {
  try {
    await dashboardStore.getAllDiseases()
    loading.value = true
    loading.value = false
    error_get.value = false
  } catch (error: any) {
    message.error(t('common.errorSomeThing') + " " + error.message);
    console.error(t('common.errorSomeThing'), error.message);
    console.error(error_get.value);
    error_get.value = true
    console.error(error_get.value);
    loading.value = false

  }
}



onMounted(async () => {
 getDataAsync();
})
const dataTableInstRef = ref(null)
const dataTableInst = dataTableInstRef


function handleUpdateFilter(
  filters: DataTableFilterState,
  sourceColumn: DataTableBaseColumn
) {
  mainColumn.filterOptionValue = filters[sourceColumn.key] as string
}


const columnsRef = ref(columns)

function handleSorterChange(sorter: any) {
  columnsRef.value.forEach((column) => {
    if (column.sortOrder === undefined) return
    if (!sorter) {
      column.sortOrder = false
      return
    }
    if (column.key === sorter.columnKey) column.sortOrder = sorter.order
    else column.sortOrder = false
  })
}

const rowKey = (row: Dashboard.Diseases) => row.id!;

function handleCheck(rowKeys: DataTableRowKey[]) {
  checkedRowKeysRef.value = rowKeys
}

function deleteSelectedRows() {
  const deleteDialog = dialog.warning({
    title: t('chat.deleteConfirmation'),
    content: t('chat.deleteConfirmationMessage'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      try {
        deleteDialog.loading = true;
        const deletePromises = checkedRowKeysRef.value.map((id) =>
        dashboardStore.showModelUpdatePlant
        //   universityStore.deleteAction(id as unknown as number)
        );
        await Promise.all(deletePromises);
        message.success(t('chat.deleteSuccess'));
      } catch (error: any) {
        console.error(t('common.deleteFailed'), error.message);
        message.error(t('common.deleteFailed'));
      } finally {
        deleteDialog.loading = false;
      }
    },
  });
}



</script>
<template>
  <div class="container_dashboard">

    <div class="header_dashboard">
        {{ t('dashboard.diseases') }}
      </div>

  
      <div class="flex gap-2 justify-end items-center mb-2">
        <NButton
          @click="dashboardStore.showModelAdd = true"
          type="primary"
        >
          <div class="flex gap-2  items-center">
            <SvgIcon
              icon="mdi:add-bold"
              class=" text-base"
            />
            <div class="hidden md:block">{{ t('dashboard.addDiseases') }}</div>
          </div>

        </NButton>
  
        <div class="cursor-pointer">
          <FilterTable
            :mainColumn="mainColumn"
            :columns="columns"
          />
        </div>
      </div>

    <div class="">
      <NSpace
        vertical
        :size="12"
      >
        <template v-if="error_get">
          <div class=" border-red-400 bg-red-100 p-4 rounded-lg ">
          
  <NResult status="error" title="Error" :description="t('common.errorSomeThing')">
    <template #footer>
      <NButton
                size="small"
                :loading="loading"
                @click="getDataAsync()"
              >
          {{t('app.tryAgain')}}
              </NButton>
    </template>
  </NResult>
        </div>
        </template>
        <template v-if="!error_get">
          <NDataTable
          remote
            :size="isMobile ? 'small' : 'small'"
            striped
            :loading="loading"
            ref="dataTableInst"
            :columns="columns"
            :data="data"
            :pagination="pagination"
            :max-height="isMobile ? 400 : 370"
            :min-height="isMobile ? 380 : 370"
            :paginate-single-page=false
            v-model:checked-row-keys="checkedRowKeysRef"
            @update:filters="handleUpdateFilter"
            @update:sorter="handleSorterChange"
            :row-key="rowKey"
            @update:checked-row-keys="handleCheck"
          />
        </template>
      </NSpace>
    </div>
  </div>

  <div>
    <NModal
      v-model:show="dashboardStore.showModelAdd"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 640px;"
    >
      <AddDiseases/>
    </NModal>
    <NModal
      v-model:show="dashboardStore.showModelUpdatePlant"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 640px;"
    >
      <UpdateUniversity
        :row="rowEdit!"
      
      />
    </NModal>

</div>

</template>

<style scoped>
:deep(th) {
  font-weight: bold;
font-size: 1rem;

}
</style>