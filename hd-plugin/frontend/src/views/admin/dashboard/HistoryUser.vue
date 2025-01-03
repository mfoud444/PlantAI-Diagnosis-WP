<script
  setup
  lang='ts'
>
import { ref, h, onMounted, computed, reactive } from 'vue'
import {
  NSpace, NButton, NDataTable, DataTableBaseColumn,
  useDialog, NResult,NAvatar,
  DataTableRowKey, 
  useMessage, DataTableFilterState, DataTableColumns,
} from 'naive-ui'

import { useDashboardStore } from '@/store'
import { t } from '@/locales';
import { useIconRender } from '@/hooks/useIconRender'
import { useBasicLayout } from '@/hooks/useBasicLayout';

import { SvgIcon } from '@/components/common';

const dashboardStore = useDashboardStore()
const { iconRender } = useIconRender()
const loadingActionDelete = ref(false)
const loading = ref(true)
const error_get = ref<boolean>(false)

const checkedRowKeysRef = ref<Array<string | number>>([])
const { isMobile } = useBasicLayout()
const dialog = useDialog()
const message = useMessage();
type TableData = {
  id:string

  pictureUrl:string
}

const data = computed(() => dashboardStore.listHistoryUser);





const pageSize: number = 3
const pages = computed(() => 3)
const pagination = reactive({
  page: pages!,
  pageSize: pageSize,
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
function handleDeleteAction(id: string, type: string) {
  const deleteDialog = dialog.warning({
    title: t('chat.deleteConfirmation'),
    content: t('chat.deleteConfirmationMessage'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      try {
        deleteDialog.loading = true
        if (type === 'plant') {
          await dashboardStore.deletePlantsAction(id)
        }
        else if (type === 'disease') {
          await dashboardStore.deleteDiseasesAction(id)
        }

        message.success(t('chat.deleteSuccess'));
      } catch (error: any) {
        deleteDialog.loading = false
        message.error(t('chat.deleteFailed'));
        console.error(error.message)
      } finally {
        deleteDialog.loading = false
      }
    },
  });
}



const columns = reactive<DataTableColumns<TableData>>([


  {
      title:  t('dashboard.idUser'),
      key: 'idUser'
    },
    {
      title: t('dashboard.namePlant'),
      key: 'plantName'
    },
    {
      title: t('dashboard.picture'),
      key: 'picture',
      render(row: TableData) {
      return h(
        'div',
        {
          class: 'flex gap-1'
        },
        [
          h(
            NAvatar,
            {
              round: true,
        size:"medium",
         
        src:row.pictureUrl
           
            },
          
          ),

        ]
      );
    }
    },

    {
      title: t('dashboard.result'),
      key: 'predictionResultValue'
    },

    {
      title: t('dashboard.nameDiseases'),
      key: 'diseaseName'
    },



]);



async function getDataAsync() {
  try {
    await dashboardStore.getAllHistoryUserAction()

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

  if(dashboardStore.listHistoryUser.length == 0 ){
    getDataAsync();
    console.log(dashboardStore.listHistoryUser)
  } else {
    loading.value = true
    loading.value = false
    error_get.value = false
  }


})
const dataTableInstRef = ref(null)
const dataTableInst = dataTableInstRef


function handleUpdateFilter(
  filters: DataTableFilterState,
  sourceColumn: DataTableBaseColumn
) {
  // plantsColumn.filterOptionValue = filters[sourceColumn.key] as string
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

const rowKey = (row: TableData) => row.id!;

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
          dashboardStore.deletePlantsAction(id as unknown as string)

        );
        await Promise.all(deletePromises);
        message.success(t('common.deleteSuccess'));
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
      {{ t('dashboard.historyUser') }}
    </div>

    <div class="flex gap-2 justify-end items-center mb-2">


 </div>
    </div>

    <div class="">
      <NSpace
        vertical
        :size="12"
      >
        <template v-if="error_get">
          <div class=" border-red-400 bg-red-100 p-4 rounded-lg ">

            <NResult
              status="error"
              title="Error"
              description="It's red"
            >
              <template #footer>
                <NButton
                  size="small"
                  :loading="loading"
                  @click="getDataAsync()"
                >
                  {{ t('app.tryAgain') }}
                </NButton>
              </template>
            </NResult>
          </div>
        </template>

 
        <template v-if="!error_get">
          <NDataTable
          :scroll-x="isMobile ? 800 : 0"
            remote
            :single-line="false"
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


</template>

