<template>
  <div class="app-container">
    <el-button :disabled="btns.add" @click="handleCreate()"> + 设置</el-button>
    <el-button @click="fieldEdit = fieldEdit ? false : true">
      <span v-if="!fieldEdit">+ 字段编辑</span>
      <span v-else>- 字段编辑</span>
    </el-button>

    <el-button
      v-waves
      :loading="refreshLoading"
      class="filter-item"
      type="primary"
      icon="el-icon-refresh"
      @click="handleRefresh"
    >刷新缓存</el-button>

    <el-form
      ref="dataForm2"
      label-position="left"
      label-width="200px"
      style="width: 700px; margin-left:50px;"
    >
      <div
        v-for="(item, i) in list"
        :key="i"
      >
        <el-form-item
          v-if="item.type!='Cate'"
          :label="item.mark"
        >
          <el-input v-model="item.val" :placeholder="item.field" />

          {{ item.type }} | {{ item.field }}
          <a v-if="fieldEdit" @click="handleUpdate(item)"><i class="el-icon-edit" /></a>

          <a v-if="fieldEdit" @click="handleDelete(item)"><i class="el-icon-delete" /></a>
        </el-form-item>
        <el-divider v-else content-position="left">{{ item.mark }}
          <a v-if="fieldEdit" @click="handleUpdate(item)"><i class="el-icon-edit" /></a>

          <a v-if="fieldEdit" @click="handleDelete(item)"><i class="el-icon-delete" /></a>
        </el-divider>
      </div>

      <el-form-item>
        <el-button
          type="primary"
          :loading="submitLoading"
          @click="handleBatchValSave"
        >保存</el-button>
      </el-form-item>
    </el-form>

    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-form-item
          label="设置类型"
          prop="type"
          :error="form.errors.has('type') ? form.errors.get('type') : ''"
        >
          <el-select
            v-model="form.type"
            class="filter-item"
            placeholder="请选择"
            @change="handleChangeCate"
          >
            <el-option
              v-for="(item, index) in settingTypeOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>

        <el-form-item
          v-show="parentDisabled"
          label="父类"
          prop="parent_id"
          :error="form.errors.has('parent_id') ? form.errors.get('parent_id') : ''"
        >
          <el-select v-model="form.parent_id" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in settingCateOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>
        <el-form-item
          label="描述"
          prop="mark"
          :error="form.errors.has('mark') ? form.errors.get('mark') : ''"
        >
          <el-input v-model="form.mark" placeholder="描述" />
        </el-form-item>

        <el-form-item
          label="字段名称"
          prop="field"
          :error="form.errors.has('field') ? form.errors.get('field') : ''"
        >
          <el-input v-model="form.field" placeholder="字段名称" />
        </el-form-item>

        <el-form-item
          label="值"
          prop="val"
          :error="form.errors.has('val') ? form.errors.get('val') : ''"
        >
          <el-input v-model="form.val" placeholder="值" />
        </el-form-item>

        <el-form-item
          label="排序"
          prop="sorted"
          :error="form.errors.has('sorted') ? form.errors.get('sorted') : ''"
        >
          <el-input v-model="form.sorted" placeholder="排序" />
        </el-form-item>

        <el-form-item
          label="扩展数据"
          prop="options"
          :error="form.errors.has('options') ? form.errors.get('options') : ''"
        >
          <el-input v-model="form.options" placeholder="扩展数据" />
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="submitLoading"
          @click="dialogStatus==='create'?createData():updateData()"
        >确定</el-button>
        <el-button
          v-if="dialogStatus==='create'"
          type="primary"
          :loading="submitLoading"
          @click="create2Data()"
        >连续添加</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { disable, allow } from '@/utils'
import { getSettingRefresh } from '@/api/role'
export default {
  name: 'ComplexTable',
  directives: { waves },
  data() {
    return {
      btns: {
        add: true,
        edit: true,
        delete: true,
        search: true,
        batchVal: true
      },
      filterText: '',
      list: [],
      searchForm: new Form({}),
      submitLoading: false,
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '新增'
      },
      form: new Form({
        id: 0,
        type: '',
        field: '',
        val: '',
        sorted: 0,
        mark: '',
        options: '',
        parent_id: 0
      }),
      optionForm: new Form({
        ins: 'settingType,settingCate'
      }),
      settingTypeOption: [],
      settingCateOption: [],
      parentDisabled: false,
      fieldEdit: false,
      refreshLoading: false
    }
  },
  watch: {
    filterText(val) {
      this.$refs.menu.filter(val)
    }
  },
  mounted() {
    const allowOpen = allow('M:/setting/setting')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/setting')
      this.btns.edit = disable('N:Put:/setting/{id}')
      this.btns.delete = disable('N:Delete:/setting/{id}')
      this.btns.search = disable('N:Get:/setting')
      this.btns.batchVal = disable('N:Patch:/batchVal')
      const allowGetOption = allow('N:Get:/option')
      if (this.btns.search === false) {
        this.getList()
      }

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      // this.$router.push('/401')
    }
  },

  methods: {
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    getList() {
      this.searchForm
        .get('/setting')
        .then(({ data }) => {
          this.list = []
          this.list = data.data
        })
        .catch(() => {
        })
    },
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.settingTypeOption = data.data.settingType
        this.settingCateOption = data.data.settingCate
      })
    },
    handleBatchValSave() {
      this.submitLoading = true
      const kv = {}
      for (const i in this.list) {
        // console.log(this.list[i].field, this.list[i].val)
        kv[this.list[i].field] = this.list[i].val
      }

      const f = new Form(kv)
      f.patch('/setting/batchVal').then(({ data }) => {
        this.$notify({
          title: 'Success',
          message: data.meta.message,
          type: 'success',
          duration: 2000
        })
        this.submitLoading = false
      }).catch(() => {
        this.submitLoading = false
      })
    },
    handleChangeCate() {
      if (this.form.type === 'Cate') {
        this.parentDisabled = false
      } else {
        this.parentDisabled = true
      }
    },
    handleCreate(data) {
      this.form.reset()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
    },
    create2Data() {
      this.submitLoading = true
      this.form
        .post('/setting')
        .then(({ data }) => {
        //   this.dialogFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
          this.getList()
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    createData() {
      this.submitLoading = true
      this.form
        .post('/setting')
        .then(({ data }) => {
          this.dialogFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
          this.getList()
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    handleUpdate(row) {
      this.form.reset()
      this.form.fill(row)
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
    },
    updateData() {
      this.submitLoading = true
      this.form
        .put('/setting/' + this.form.id)
        .then(({ data }) => {
          this.dialogFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
          this.getList()
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    handleDelete(row) {
      this.form.fill(row)
      this.$confirm(
        '此操作将永久删除【' + this.form.mark + '】, 是否继续?',
        '提示',
        {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }
      )
        .then(() => {
          this.listLoading = true
          this.form
            .delete('/setting/' + this.form.id)
            .then(({ data }) => {
              this.$notify({
                title: 'Success',
                message: data.meta.message,
                type: 'success',
                duration: 2000
              })
              this.getList()
              this.form.reset()
              this.listLoading = false
            })
            .catch(() => {
              this.listLoading = false
            })
        })
        .catch(() => {
          // this.$message({
          //   type: 'info',
          //   message: '已取消删除'
          // });
        })
    },
    handleRefresh() {
      this.refreshLoading = true
      getSettingRefresh().then(({ data }) => {
        this.$notify({
          title: 'Success',
          message: data.meta.message,
          type: 'success',
          duration: 2000
        })
        this.refreshLoading = false
      }).catch(() => {
        this.refreshLoading = false
      })
    }
  }
}
</script>
