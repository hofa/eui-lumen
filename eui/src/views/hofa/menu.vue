<template>
  <div class="app-container">
    <el-input
      v-model="filterText"
      placeholder="输入关键字进行过滤"
    />

    <el-divider />
    <el-button :disabled="btns.add" @click="handleCreate(0)"> + 根节点</el-button>
    <el-tree
      ref="menu"
      :data="menusData"
      node-key="id"
      default-expand-all
      :expand-on-click-node="false"
      :filter-node-method="filterNode"
    >
      <span slot-scope="{ node, data }" class="custom-tree-node">
        <span>
          <i v-if="data.display=='Normal' && data.type=='Menu' && data.status=='Normal'" class="el-icon-view" />
          <i v-if="data.status=='Close'" class="el-icon-delete-solid" />
          {{ node.label }}
        </span>
        <span>
          <el-button
            v-if="data.type == 'Menu'"
            type="text"
            size="mini"
            :disabled="btns.add"
            @click="() => handleCreate(data)"
          >
            <i class="el-icon-plus" />
          </el-button>
          <el-button
            type="text"
            size="mini"
            :disabled="btns.edit"
            @click="() => handleUpdate(data)"
          >
            <i class="el-icon-edit" />
          </el-button>
          <el-button
            type="text"
            size="mini"
            :disabled="btns.delete"
            @click="() => handleDelete(data)"
          >
            <i class="el-icon-delete" />
          </el-button>
        </span>
      </span>
    </el-tree>

    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-form-item
          label="ParentId"
          prop="parent_id"
          :error="form.errors.has('parent_id') ? form.errors.get('parent_id') : ''"
        >
          <el-input v-model="form.parent_id" placeholder="ParentId" disabled />
        </el-form-item>

        <el-form-item
          label="标题"
          prop="title"
          :error="form.errors.has('title') ? form.errors.get('title') : ''"
        >
          <el-input v-model="form.title" placeholder="标题" />
        </el-form-item>

        <el-form-item
          label="路径"
          prop="path"
          :error="form.errors.has('path') ? form.errors.get('path') : ''"
        >
          <el-input v-model="form.path" placeholder="路径" />
        </el-form-item>

        <el-form-item
          label="图标"
          prop="icon"
          :error="form.errors.has('icon') ? form.errors.get('icon') : ''"
        >
          <el-input v-model="form.icon" placeholder="图标" />
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
          prop="extends"
          :error="form.errors.has('extends') ? form.errors.get('extends') : ''"
        >
          <el-input v-model="form.extends" placeholder="扩展数据" />
        </el-form-item>

        <el-form-item
          label="状态"
          prop="status"
          :error="form.errors.has('status') ? form.errors.get('status') : ''"
        >
          <el-select v-model="form.status" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in statusOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>

        <el-form-item
          label="显示"
          prop="display"
          :error="form.errors.has('display') ? form.errors.get('display') : ''"
        >
          <el-select v-model="form.display" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in displayOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>

        <el-form-item
          label="类型"
          prop="type"
          :error="form.errors.has('type') ? form.errors.get('type') : ''"
        >
          <el-select v-model="form.type" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in typeOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>

        <el-form-item
          label="请求类型"
          prop="request_type"
          :error="form.errors.has('request_type') ? form.errors.get('request_type') : ''"
        >
          <el-select v-model="form.request_type" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in requestTypeOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
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
// import { mapGetters } from 'vuex'
export default {
  name: 'ComplexTable',
  directives: { waves },
  data() {
    return {
      btns: {
        add: true,
        edit: true,
        delete: true,
        search: true
      },
      filterText: '',
      menusData: [],
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
        parent_id: 0,
        title: '',
        path: '',
        icon: '',
        type: '',
        request_type: '',
        sorted: '',
        extends: '',
        display: '',
        status: ''
      }),
      optionForm: new Form({
        ins: 'status,display,menuType,requestType'
      }),
      statusOption: [],
      displayOption: [],
      typeOption: [],
      requestTypeOption: []
    }
  },
  watch: {
    filterText(val) {
      this.$refs.menu.filter(val)
    }
  },
  mounted() {
    const allowOpen = allow('M:/setting/menu')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/menu')
      this.btns.edit = disable('N:Put:/menu/{id}')
      this.btns.delete = disable('N:Delete:/menu/{id}')
      this.btns.search = disable('N:Get:/menu')
      const allowGetOption = allow('N:Get:/option')
      if (this.btns.search === false) {
        this.getList()
      }

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      this.$router.push('/401')
    }
  },

  methods: {
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    getList() {
      this.searchForm
        .get('/menu')
        .then(({ data }) => {
          this.menusData = []
          this.menusData = data.data
        })
        .catch(() => {
        })
    },
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.statusOption = data.data.status
        this.displayOption = data.data.display
        this.typeOption = data.data.menuType
        this.requestTypeOption = data.data.requestType
      })
    },
    handleCreate(data) {
      this.form.reset()
      this.form.parent_id = data !== 0 ? data.id : 0
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
    },
    create2Data() {
      this.submitLoading = true
      this.form
        .post('/menu')
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
        .post('/menu')
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
        .put('/menu/' + this.form.id)
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
        '此操作将永久删除【' + this.form.title + '】菜单, 是否继续?',
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
            .delete('/menu/' + this.form.id)
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
    }
  }
}
</script>
