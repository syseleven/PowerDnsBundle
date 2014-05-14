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
 * Defines the base form for creating and updating a domain object
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class DomainsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type','text')
            ->add('master','text', array('required' => false))
            ->add('account', 'text', array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Entity\Domains',
        ));
    }

    public function getName()
    {
        return '';
    }
}