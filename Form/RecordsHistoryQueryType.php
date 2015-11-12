<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
namespace SysEleven\PowerDnsBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class RecordsHistoryQueryType
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class RecordsHistoryQueryType extends AbstractType
{
    /**
     * @var array
     */
    protected $supported = array("SOA", "A", "AAAA", "ASFB", "CERT", "CNAME",
                                 "DNSKEY", "DS", "HINFO", "KEY", "LOC", "MX", "NAPTR",
                                 "NS", "NSEC", "PTR", "RP", "RRSIG", "SPF", "SSHFP",
                                 "SRV","TXT");


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search','text', array('required' => false))
            ->add('domain_id', 'entity', array('required' => false, 'class' => 'SysElevenPowerDnsBundle:Domains', 'multiple' => true))
            ->add('user','text', array('required' => false))
            ->add('record_type','choice', array('choices' => $this->getSupportedTypes(),'multiple' => true))
            ->add('from','datetime',array('required' => false))
            ->add('to','datetime',array('required' => false))
            ->add('action','choice',array('required' => false, 'choices' => array('CREATE' => 'CREATE', 'UPDATE' => 'UPDATE', 'DELETE' => 'DELETE')))
         ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Query\RecordsHistoryQuery',
            ));
    }


    public function getName()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getSupportedTypes()
    {
        $r = array();
        foreach ($this->supported AS $v) {
            $r[$v] = $v;
        }

        return $r;
    }

}
 