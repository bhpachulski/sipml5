<table class="table table-hover">
                        <thead class="thead-inverse">
                            <tr>
                                <td>id</td>
                                <td>Nome</td>
                                <td>Telefone</td>
                                <td></td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php 

                            require_once '../webadmin/lib/Util.php';
                            require_once '../webadmin/lib/banco/BD.php';

                            $c = BD::getInstance();
                            $arr = array();

                            $sql =  'SELECT TOP 100 id, nome, email, telefone FROM SysWeb.dbo.vestibularPreInscricao WHERE telefone is not null order by nome asc';

                            $q = $c->query($sql);

                            while ($obj = $c->fetch_object($q)) {

                                ?>        

                                <tr>

                                    <?php echo '<td>' . $obj->id . '</td>'; ?>
                                    <?php echo '<td>' . $obj->nome . '</td>'; ?>
                                    <?php echo '<td class="celular">' . $obj->telefone . '</td>'; ?>  

                                    <td><a href="#" class="toCall"> CALL </a></td>

                                </tr>

                                <?php
                            }

                            ?>
                        </tbody>
                    </table>