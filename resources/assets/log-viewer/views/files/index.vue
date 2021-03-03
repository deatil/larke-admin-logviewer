<template>
  <div class="app-container">
    <el-row :gutter="40" class="panel-group">
      <el-col :xs="8" :sm="8" :lg="6" class="card-panel-col">
        <el-card>
          <div slot="header" class="clearfix">
              <span>日志文件</span>
          </div>    

          <div class="filter-container">
              <el-input 
                v-model="file.listQuery.searchword" 
                placeholder="关键字" 
                clearable 
                style="width: auto;" 
                class="filter-item" />

              <el-select 
                v-model="file.listQuery.order" 
                placeholder="排序" 
                class="filter-item" 
                style="width: 110px;margin-right:10px">
                  <el-option 
                    v-for="sort in file.sortOptions" 
                    :key="sort.key" 
                    :label="sort.label" 
                    :value="sort.key" />
              </el-select>

              <el-button 
                v-waves 
                class="filter-item" 
                type="primary" 
                style="width: 80px;"
                @click="getFileList">
                查询
              </el-button>
          </div>

          <div class="log-files">
            <el-table
              v-loading="file.listLoading"
              :header-cell-style="{background:'#eef1f6',color:'#606266'}"
              :data="file.list"
              border
              fit
              highlight-current-row
              style="width: 100%"
            >
              <el-table-column width="auto" label="文件列表">
                <template slot-scope="scope">
                  <el-tooltip effect="dark" :content="scope.row.time" placement="top">
                    <span class="log-file" @click="showFileLog($event, scope.row, scope.$index)">
                      {{ scope.row.name }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
            </el-table>

            <div class="file-pagination" v-if="file.pagetotal > 1">
              <el-button
                v-waves 
                type="primary"
                style="width:45%;"
                size="mini"
                :disabled="!(file.listQuery.page > 1)"
                @click="handleFilePrev"
              >
                上一页
              </el-button>  

              <el-button
                v-waves 
                type="primary"
                style="width:45%;"
                size="mini"
                :disabled="!(file.listQuery.page < file.pagetotal)"
                @click="handleFileNext"
              >
                下一页
              </el-button> 
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="16" :sm="16" :lg="18" class="card-panel-col">
        <el-card>
          <div slot="header" class="clearfix">
              <span>日志列表</span>
          </div>    

          <div class="log-container">
            <div class="log-file-detail">
              <el-row :gutter="0" class="panel-group">
                <el-col :xs="18" :sm="18" :lg="18" class="card-panel-col">
                  <div class="log-file-detail-item">
                    文件：<span>{{ file.detail.name }}</span>
                  </div>
                  <div class="log-file-detail-item">
                    大小：<span>{{ file.detail.size }}</span>
                  </div>
                  <div class="log-file-detail-item">
                    最后更新：<span>{{ file.detail.time }}</span>
                  </div>
                </el-col>

                <el-col :xs="6" :sm="6" :lg="6" class="card-panel-col" v-if="log.listQuery.file != ''">
                  <div class="log-file-action-item">
                    <el-button
                      v-waves 
                      type="danger"
                      style="width:100px;"
                      size="mini"
                      :disabled="!checkPermission(['larke-admin.log-viewer.delete'])"
                      @click="handleDeleteFile(log.listQuery.file, $event)"
                    >
                      删除
                    </el-button>  
                  </div>

                  <div class="log-file-action-item">
                    <el-button
                      v-waves 
                      type="success"
                      style="width:100px;"
                      size="mini"
                      :disabled="!checkPermission(['larke-admin.log-viewer.download'])"
                       @click="handleDownloadFile(log.listQuery.file, $event)"
                    >
                      下载
                    </el-button>  
                  </div>
                </el-col>
              </el-row>
            </div>

            <div class="log-content">
              <div class="log-files">
                <el-table
                  v-loading="log.listLoading"
                  :header-cell-style="{background:'#eef1f6',color:'#606266'}"
                  :data="log.list"
                  border
                  fit
                  highlight-current-row
                  style="width: 100%"
                >
                  <el-table-column width="160px" label="时间">
                    <template slot-scope="scope">
                        {{ scope.row.time }}
                    </template>
                  </el-table-column>

                  <el-table-column width="85px" align="center" label="等级">
                    <template slot-scope="scope">
                      <el-tag :type="scope.row.level | levelFilter" size="mini">
                        {{ scope.row.level }}
                      </el-tag>
                    </template>
                  </el-table-column>

                  <el-table-column width="80px" align="center" label="存储">
                    <template slot-scope="scope">
                      {{ scope.row.env }}
                    </template>
                  </el-table-column>

                  <el-table-column label="内容">
                    <template slot-scope="scope">
                      {{ scope.row.info }}
                    </template>
                  </el-table-column>

                  <el-table-column align="center" label="操作" width="120">
                    <template slot-scope="scope">
                      <el-button  type="info" size="mini" icon="el-icon-info" @click="handleLogDetail(scope.$index, scope.row)">
                        详情
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>

              <div class="log-pagination" v-if="log.prev || log.next">
                <el-button
                  v-waves 
                  type="primary"
                  style="width:100px;"
                  size="mini"
                  :disabled="!log.prev"
                  @click="handleLogPrev"
                >
                  上一页
                </el-button>  

                <el-button
                  v-waves 
                  type="primary"
                  style="width:100px;"
                  size="mini"
                  :disabled="!log.next"
                  @click="handleLogNext"
                >
                  下一页
                </el-button> 
              </div>

            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-dialog title="日志详情" :visible.sync="detail.dialogVisible">
      <detail :data="detail.data" />
    </el-dialog>
  </div>
</template>

<script>
import waves from '@/directive/waves'
import { scrollTo } from '@/utils/scroll-to'
import Pagination from '@/components/Pagination'
import permission from '@/directive/permission/index.js' // 权限判断指令
import checkPermission from '@/utils/permission' // 权限判断函数
import Detail from '@/components/Larke/Detail'
import { 
  getFiles,
  getLogs,
  deleteLog,
  getDownloadUrl
 } from '../../api/index'

export default {
  name: 'LogViewerIndex',
  components: { Pagination, Detail },
  directives: { waves, permission },
  filters: {
    levelFilter(level) {
      const map = {
        'ALERT': 'danger',
        'CRITICAL': 'warning',
        'DEBUG': 'info',
        'EMERGENCY': 'danger',
        'ERROR': 'danger',
        'INFO': 'info',
        'LOG': 'info',
        'NOTICE': 'info',
        'WARNING': 'warning',
        'WRITE': 'warning'
      }
      return map[level]
    },
  },
  data() {
    return {
      file: {
        list: null,
        total: 0,
        pagetotal: 0,
        listLoading: true,
        listQuery: {
          searchword: '',
          order: 'time',
          page: 1,
          pagesize: 120
        },
        sortOptions: [
          { key: 'name', label: '名称', },
          { key: 'time', label: '时间', },
        ],
        detail: {
          name: '--',
          size: '--',
          time: '--',
        }
      },
      log: {
        list: null,
        prev: null,
        next: null,
        listLoading: false,
        listQuery: {
          file: '',
          offset: 0
        },
      },
      detail: {
        dialogVisible: false,
        data: []
      },
    }
  },
  created() {
    this.getFileList()
  },
  methods: {
    checkPermission,
    async getFileList() {
      this.file.listLoading = true

      await getFiles({
        keywords: this.file.listQuery.searchword,
        order: this.file.listQuery.order,
        page: this.file.listQuery.page,
        pagesize: this.file.listQuery.pagesize
      }).then(response => {
        this.file.list = response.data.list
        this.file.total = response.data.total
        this.file.pagetotal = parseInt(this.file.total / this.file.listQuery.pagesize)
        this.file.listLoading = false
      })
    },
    async getLogList() {
      this.log.listLoading = true

      await getLogs({
        file: this.log.listQuery.file,
        offset: this.log.listQuery.offset,
      }).then(response => {
        this.log.list = response.data.list
        this.log.prev = response.data.prev
        this.log.next = response.data.next

        this.log.listLoading = false
      })
    },
    handleFilePrev() {
      this.file.listQuery.page -= 1
      this.getFileList()
      scrollTo(0, 800)
    },
    handleFileNext() {
      this.file.listQuery.page += 1
      this.getFileList()
      scrollTo(0, 800)
    },
    showFileLog(event, row, index) {
      if (this.file.detail.name == row.name) {
        return ;
      }

      this.file.detail.name = row.name
      this.file.detail.size = row.format_size
      this.file.detail.time = row.time

      this.log.listQuery.file = row.name
      this.log.listQuery.offset = 0

      this.getLogList()
    },
    handleLogDetail(event, row) {
      this.detail.dialogVisible = true

      this.detail.data = [
        {
          name: '时间',
          content: row.time,
          type: 'time'
        },
        {
          name: '等级',
          content: row.level,
          type: 'text'
        },
        {
          name: '存储',
          content: row.env,
          type: 'text'
        },
        {
          name: '内容',
          content: row.info,
          type: 'text'
        },
        {
          name: '调试',
          content: row.trace.replace(/[\n]/g, "<br/>"),
          type: 'html'
        },
      ]
    },
    handleLogPrev() {
      this.log.listQuery.offset = this.log.prev
      this.getLogList()
      scrollTo(0, 800)
    },
    handleLogNext() {
      this.log.listQuery.offset = this.log.next
      this.getLogList()
      scrollTo(0, 800)
    },
    handleDeleteFile(file) {
      if (file == '') {
        this.$message({
          message: '请选择要删除的文件',
          type: 'error',
          duration: 3 * 1000
        })
        return
      }

      const thiz = this
      this.$confirm('确认要删除该日志文件吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleteLog({
          file: file
        }).then(res => {
          this.$message({
            message: res.message,
            type: 'success',
            duration: 3 * 1000
          })
        })
      }).catch(() => {})
    },
    handleDownloadFile(file) {
      if (file == '') {
        this.$message({
          message: '请选择要下载的文件',
          type: 'error',
          duration: 3 * 1000
        })
        return
      }

      const url = getDownloadUrl(file)
      window.open(url, '_blank')
    },  
  }
}
</script>

<style scoped>
.file-pagination {
  margin-top: 10px;
  text-align: center;
}
.log-file {
  cursor: pointer;
}
.log-file-detail {
  border-radius: 4px;
  background-color: #eef1f6;
  padding: 15px 10px;
  margin-bottom: 15px;
}
.log-file-detail-item {
  padding: 5px;
  font-size: 15px;
  color: #3c3f63;
}
.log-file-detail-item span {
  font-weight: bold;
}
.log-file-action-item {
  margin-bottom: 10px;
  text-align: center;
}
.log-pagination {
  margin-top: 10px;
}
</style>
