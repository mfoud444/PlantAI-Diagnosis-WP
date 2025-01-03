<script
  setup
  lang='ts'
>
import { ref, h, onMounted, computed, reactive } from 'vue'
import {
  NSpace, NButton, NDataTable, DataTableBaseColumn,
  useDialog, NResult,
  DataTableRowKey, NModal,
  useMessage, DataTableFilterState, DataTableColumns,
} from 'naive-ui'
import CardsPlants from './CardPlants.vue'
import Cards from '../diseases/Cards.vue'
import CardModelAI from '../modelai/CardModelAI.vue'
import { useDashboardStore } from '@/store'
import { t } from '@/locales';
import { useIconRender } from '@/hooks/useIconRender'
import { useBasicLayout } from '@/hooks/useBasicLayout';

import { SvgIcon } from '@/components/common';
import AddPlants from './AddPlants.vue'
import AddDiseases from '../diseases/AddDiseases.vue'
import AddModelAI from '../modelai/AddModelAI.vue'

import UpdatePlants from './UpdatePlants.vue'
import UpdateDiseases from '../diseases/UpdateDiseases.vue'
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
const rowEdit = ref<Dashboard.Plant | null>(null);
  const rowEditDisease = ref<Dashboard.Diseases | null>(null);
type TableData = {
  plant: Dashboard.Plant
  disease: Dashboard.Diseases | null

}
const allPlants = computed(() => dashboardStore.listPlants)


const data = computed(() => {
  return allPlants.value.flatMap(plant => {
    const diseases = dashboardStore.getListDiseasesByIdPlant(plant.id!);
    if (diseases.length > 0) {
      return diseases.map(disease => ({
        plant,
        disease
      }));
    } else {
      return [{
        plant: plant,
        disease: null
      }];
    }
  });
});




function getCountRowPlant(id: string): number {
  const diseases = dashboardStore.getListDiseasesByIdPlant(id);

  return diseases.length
}
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
    title: t('common.deleteConfirmation'),
    content: t('common.deleteConfirmationMessage'),
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

        message.success(t('common.deleteSuccess'));
      } catch (error: any) {
        deleteDialog.loading = false
        message.error(t('common.deleteFailed'));
        console.error(error.message)
      } finally {
        deleteDialog.loading = false
      }
    },
  });
}

async function handleUpdatePlant(row: Dashboard.Plant) {
  dashboardStore.showModelUpdatePlant = true;
  rowEdit.value = row;
}

async function handleUpdateDisease(row: Dashboard.Diseases) {
  dashboardStore.showModelUpdateDiseases = true;
  rowEditDisease.value = row;
}

const plantsColumn = reactive<DataTableBaseColumn<TableData>>({
  title: t('dashboard.plant'),
  width: 180,
  className:'bg-red-200',
  key: 'plants',
  rowSpan: (rowData, rowIndex) => (rowData.disease?.plantId === rowData.plant.id ? getCountRowPlant(rowData.plant.id!) : 1),
  render(row: TableData) {
    return h(
      CardsPlants,
      {
        row: row.plant
      },
    )
  },
})



const ModelAIColumn = reactive<DataTableBaseColumn<TableData>>({
  title: t('dashboard.modelAI'),
  align: 'center',
  width: 200,
  key: 'modelai',
  rowSpan: (rowData, rowIndex) => (rowData.disease?.plantId === rowData.plant.id ? getCountRowPlant(rowData.plant.id!) : 1),
  render(row: TableData) {
    try {
      const modelAI = dashboardStore.getListModelAIByIdPlant(row.plant.id!);

      if (modelAI) {
        return h(CardModelAI, { row: modelAI });
      } else {
        return h('div', t('dashboard.noModel'));
      }
    } catch (error) {
      console.error('Error rendering model AI:', error);
      // Display an error message or handle the error gracefully
      return h('div', 'Error rendering model AI.');
    }
  },
});
const diseasesColumn = reactive<DataTableBaseColumn<TableData>>({
  title: t('dashboard.disease'),
  width: 500,
  key: 'diseases',
  render(row: TableData) {
    try {

      if (!row.disease) {
        // Render a placeholder or error message when disease is null
        return h('div',t('dashboard.noDisease'));
      }

      return h(
        'div',
        {
          class: 'flex justify-between'
        },
        [
          h(
            Cards,
            { row: row.disease }
          ),
          h(
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
                  disabled:false,
                  loading: loadingActionEdit.value,
                  style: "border-radius: 100%",
                  onClick: async () => {
                    try {
                      if (row.disease) {await handleUpdateDisease(row.disease)};
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
                  disabled:false,
                  size: 'small',
                  loading: loadingActionDelete.value,
                  onClick: () => handleDeleteAction(row.disease?.id!, 'disease')
                },
                { default: () => h(iconRender({ icon: 'fluent:delete-32-regular', color: 'red' })) }
              ),
            ]
          )
        ]
      );
    } catch (error) {
      console.error('Error fetching diseases:', error);
      // Display an error message or handle the error gracefully
      return h('div', 'Error fetching diseases for this plant.');
    }
  },
});


const columns = reactive<DataTableColumns<TableData>>([
  {
    type: 'selection',
    rowSpan: (rowData, rowIndex) => (rowData.disease?.plantId === rowData.plant.id ? getCountRowPlant(rowData.plant.id!) : 1),


  },
  plantsColumn,
  diseasesColumn,
  ModelAIColumn,
  {
    title: t('app.actions'),
    key: 'actions',
    align: 'center',
    width: 100,
    rowSpan: (rowData, rowIndex) => (rowData.disease?.plantId === rowData.plant.id ? getCountRowPlant(rowData.plant.id!) : 1),
    render(row: TableData) {
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
              disabled:false,
              size: 'small',
              loading: loadingActionEdit.value,
              style: "border-radius:100%",
              onClick: async () => {
                try {
                  await handleUpdatePlant(row.plant);
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
              disabled:false,
              size: 'small',
              loading: loadingActionDelete.value,
              onClick: () => handleDeleteAction(row.plant.id!, 'plant')
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
    await dashboardStore.getAllPlantAdminAction()
    await dashboardStore.getAllDiseasesAdmin()
    await dashboardStore.getAllModelAI()
    loading.value = true
    loading.value = false
    console.log("dashboardStore.listDiseases")
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

  if(dashboardStore.listPlants.length == 0 ){
    getDataAsync();
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
  plantsColumn.filterOptionValue = filters[sourceColumn.key] as string
}


const columnsRef = ref(columns)



const rowKey = (row: TableData) => row.plant.id!;

function handleCheck(rowKeys: DataTableRowKey[]) {
  checkedRowKeysRef.value = rowKeys
}

function deleteSelectedRows() {
  const deleteDialog = dialog.warning({
    title: t('common.deleteConfirmation'),
    content: t('common.deleteConfirmationMessage'),
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
      {{ t('dashboard.plants') }}
    </div>


    <div class="flex gap-2 justify-end items-center mb-2">
      <NButton
      style="background-color: #208000;"
        @click="dashboardStore.showModelAdd = true"
        type="primary"
      >
        <div class="flex gap-2  items-center">
          <SvgIcon
            icon="ph:plant-fill"
            class=" text-base"
          />
          <div class="hidden md:block">{{ t('dashboard.addPlants') }}</div>
        </div>

      </NButton>

      <NButton
      style="background-color: #208000;"
        @click="dashboardStore.showModelAddDiseases = true"
        type="primary"
        :disabled="data.length > 0 ? false : true"
      >
        <div class="flex gap-2  items-center">
          <SvgIcon
            icon="fa-solid:disease"
            class=" text-base"
          />
          <div class="hidden md:block">{{ t('dashboard.addDiseases') }}</div>
        </div>

      </NButton>


      <NButton
      style="background-color: #208000;"
        @click="dashboardStore.showModelAddModelAI = true"
        type="primary"
        :disabled="data.length > 0 ? false : true"
      >
        <div class="flex gap-2   items-center">
          <SvgIcon
            icon="eos-icons:ai-healing"
            class=" text-base text-white"
          />
          <div class="hidden md:block">{{ t('dashboard.modelAI') }}</div>
        </div>

      </NButton>

      <NButton
      style="background-color: red; color:white;"
        strong
        secondary
        type="error"
        :disabled="checkedRowKeysRef.length > 0 ? false : true"
        @click="deleteSelectedRows"
      >
        <div class="flex gap-2 items-center">
          <SvgIcon
            icon="fluent:delete-32-regular"
            class=" text-base"
          />
          <div class="hidden md:block">{{ t('common.delete') }}</div>
        </div>

      </NButton>

      <div class="cursor-pointer">
        <FilterTable
          :mainColumn="plantsColumn"
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
          <div class=" border-red-400 bg-red-100 p-4 rounded-sm ">

            <NResult
              status="error"
              title="Error"
              :description="t('common.errorSomeThing')"
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
            :max-height="isMobile ? 400 : 670"
            :min-height="isMobile ? 380 : 670"
            :paginate-single-page=false
            v-model:checked-row-keys="checkedRowKeysRef"
            @update:filters="handleUpdateFilter"
            :row-key="rowKey"
            @update:checked-row-keys="handleCheck"
          />
        </template>
      </NSpace>
    </div>
  </div>

  <div>

    <NModal
      v-model:show="dashboardStore.showModelAddDiseases"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 900px; margin-top:40px; margin-bottom: 40px;"
    >
      <AddDiseases />
    </NModal>

    <NModal
      v-model:show="dashboardStore.showModelAdd"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 640px;"
    >
      <AddPlants />
    </NModal>

    <NModal
      v-model:show="dashboardStore.showModelAddModelAI"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 640px;"
    >
      <AddModelAI />
    </NModal>
    <NModal
      v-model:show="dashboardStore.showModelUpdatePlant"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 640px;"
    >
      <UpdatePlants :row="rowEdit!" />
    </NModal>

    <NModal
      v-model:show="dashboardStore.showModelUpdateDiseases"
      :mask-closable=false
      :auto-focus="false"
      preset="card"
      style="width: 95%; max-width: 900px;  margin-top:40px; margin-bottom: 40px;"
    >
      <UpdateDiseases :row="rowEditDisease!" />
    </NModal>

  </div>

</template>
