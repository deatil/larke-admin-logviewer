import Layout from '@/layout'

const route = {
  path: '/log-viewer',
  component: Layout,
  redirect: '/log-viewer/files',
  alwaysShow: true,
  name: 'LogViewer',
  meta: {
    title: 'LogViewer',
    icon: 'el-icon-collection',
    roles: [
      'larke-admin.log-viewer.files',
    ]
  }, 
  children: [
    {
      path: '/log-viewer/files',
      component: () => import('./views/files/index'),
      name: 'LogViewerFiles',
      meta: {
        title: 'LogViewerFiles',
        icon: 'el-icon-document',
        roles: [
          'larke-admin.log-viewer.files'
        ]
      }
    },

  ]
}

export default route