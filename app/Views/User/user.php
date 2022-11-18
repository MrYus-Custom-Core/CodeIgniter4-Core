<?php echo $this->extend('Layout/template'); ?>

<?php echo $this->section('content'); ?>
    <?php if(session()->getFlashData('success')) : ?>
    <div class="alert alert-success" role="alert">
        <?php echo session()->getFlashdata('success'); ?>
    </div>
    <?php endif; ?>
    <?php if(session()->getFlashData('failed')) : ?>
    <div class="alert alert-danger" role="alert">
        <?php echo session()->getFlashdata('failed'); ?>
    </div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($currentPage) && isset($limit)) {?>
                    <?php $i = 1 + ($limit * ($currentPage - 1)); ?>
                <?php } else { ?>
                    <?php $i = 1; ?>
                <?php } ?>
                <?php if(!empty($user)) { ?>
                <?php foreach($user as $key => $value) : ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $value['name']; ?></td>
                        <td><?php echo $value['email']; ?></td>
                        <td><?php echo $value['address']; ?></td>
                        <td><?php echo $value['created_datetime']; ?></td>
                        <td><?php echo ($value['active_bool']) ? 'Aktif' : 'Tidak Aktif'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php } ?>
            </tbody>
        </table>
    </div>


    <div>
        <?php if(isset($currentPage)  && isset($limit)) {?>
            <?php echo $pager->links($page, 'pagination') ?>
        <?php }?>
    </div>
<?php echo $this->endSection(); ?>