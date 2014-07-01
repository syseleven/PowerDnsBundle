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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Defines the base form for a record search
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class RecordsSearchType extends AbstractType
{

    /**
     * @var array
     */
    protected $supported = array("SOA", "A", "AAAA", "ASFB", "CERT", "CNAME",
                                 "DNSKEY", "DS", "HINFO", "KEY", "LOC", "MX", "NAPTR",
                                 "NS", "NSEC", "PTR", "RP", "RRSIG", "SPF", "SSHFP",
                                 "SRV","TXT");


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'text', array('required' => false))
            ->add('search','text', array('required' => false))
            ->add('name', 'text', array('required' => false))
            ->add('name_exact','text', array('required' => false))
            ->add('content','text', array('required' => false))
            ->add('type','choice', array('choices' => array(),'multiple' => true))
            ->add('domain','entity', array('class' => 'SysElevenPowerDnsBundle:Domains', 'property' => 'id'))
            ->add('domain_id')
            ->add('managed','checkbox', array('required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Query\RecordsQuery',
            ));
    }

    /**
     * @return string
     */
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
 