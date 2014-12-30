<?php
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new SRIO\RestUploadBundle\SRIORestUploadBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/SRIORestUploadBundle/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/SRIORestUploadBundle/logs';
    }
}
